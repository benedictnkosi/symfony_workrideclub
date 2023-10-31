<?php

namespace App\Service;

use App\Entity\Commuter;
use App\Entity\CommuterAddress;
use App\Entity\CommuterMatch;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;

class MatchService
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function matchCommuter($id): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $currentCommuter = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => $id));
            if ($currentCommuter == null) {
                return array(
                    'message' => "commuter found",
                    'code' => "R01"
                );
            }

            $currentCommuter->setLastMatch(new \DateTime());
            //flush driver
            $this->em->persist($currentCommuter);
            $this->em->flush();

            $type = "passenger";
            if ($currentCommuter->getType() == "passenger") {
                $type = "driver";
            }

            $commuters = $this->em->getRepository(Commuter::class)->findBy(array('status' => "active", 'type' => $type));
            if (sizeof($commuters) == 0) {
                return array(
                    'message' => "No commuters found",
                    'code' => "R01"
                );
            }

            $travelTime = 0;
            foreach ($commuters as $commuter) {
                $this->logger->info("commuter found: " . $commuter->getId());

                $commuter->setLastMatch(new \DateTime());
                //flush driver
                $this->em->persist($commuter);
                $this->em->flush();

                //check that the commuter is not matched
                $matches = $this->em->getRepository("App\Entity\CommuterMatch")->createQueryBuilder('c')
                    ->where('c.driver = :driverId')
                    ->andWhere('c.passenger = :passengerId')
                    ->orderBy('c.additionalTime', 'ASC')
                    ->setParameter('driverId', $currentCommuter->getId())
                    ->setParameter('passengerId', $commuter->getId())
                    ->getQuery()
                    ->getResult();

                if(sizeof($matches) > 0){
                    $this->logger->info("Match found - " . $commuter->getName() . " - " . $currentCommuter->getName());
                    continue;
                }
                $driver = $currentCommuter->getType() == "driver" ? $currentCommuter : $commuter;
                $passenger = $currentCommuter->getType() == "passenger" ? $currentCommuter : $commuter;

                //if driver and passenger states are not the same then skip
                if ($driver->getHomeAddress()->getState() != $passenger->getHomeAddress()->getState()) {
                    $this->logger->info("State not the same");
                    continue;
                }

                $travelTimeResponse = $this->calculateTravelTime($driver->getHomeAddress(), $passenger->getHomeAddress(), $passenger->getWorkAddress(), $driver->getWorkAddress(), $currentCommuter->getType() == "driver");

                //write to database
                $commuterMatch = new CommuterMatch();
                $commuterMatch->setDriver($currentCommuter->getType() == "driver" ? $currentCommuter : $commuter);
                $commuterMatch->setPassenger($currentCommuter->getType() == "passenger" ? $currentCommuter : $commuter);
                $commuterMatch->setTotalTrip($travelTimeResponse["time"]);
                $commuterMatch->setDistanceHome($travelTimeResponse["driverHomeToPassengerHomeDistance"]);
                $commuterMatch->setDistanceWork($travelTimeResponse["passengerWorkToDriverDistance"]);
                $commuterMatch->setDurationHome($travelTimeResponse["driverHomeToPassengerHomeTime"]);
                $commuterMatch->setDurationWork($travelTimeResponse["passengerWorkToDriverTime"]);

                if ($currentCommuter->getType() == "passenger") {
                    $driverTravelTime = $commuter->getTravelTime();
                }else{
                    $driverTravelTime = $currentCommuter->getTravelTime();
                }

                $commuterMatch->setAdditionalTime(intval($travelTimeResponse["time"] - $driverTravelTime ));
                $commuterMatch->setStatus("active");
                $commuterMatch->setDriverStatus("pending");
                $commuterMatch->setPassengerStatus("pending");
                $commuterMatch->setMapLink($travelTimeResponse["map_link"]);
                $this->em->persist($commuterMatch);
                $this->em->flush();

            }
            return array(
                'message' => "Successfully matched commuters",
                'code' => "R00"
            );

        } catch (\Exception $e) {
            $this->logger->error("Error creating commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuters",
                'code' => "R01"
            );
        }
    }

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function unmatchCommuter($id): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $currentCommuter = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => $id));
            if ($currentCommuter == null) {
                return array(
                    'message' => "commuter not found",
                    'code' => "R01"
                );
            }

            $type = "passenger";
            if ($currentCommuter->getType() == "passenger") {
                $matches = $this->em->getRepository(CommuterMatch::class)->findBy(array('passenger' => $currentCommuter->getId()));
            }else{
                $matches = $this->em->getRepository(CommuterMatch::class)->findBy(array('driver' => $currentCommuter->getId()));
            }

            if (sizeof($matches) == 0) {
                return array(
                    'message' => "No matches found",
                    'code' => "R01"
                );
            }


            foreach ($matches as $match) {
                $this->logger->info("commuter found: " . $match->getId());

                $this->em->remove($match);
                $this->em->flush();

            }

            return array(
                'message' => "Removed all matches",
                'code' => "R00"
            );


        } catch (\Exception $e) {
            $this->logger->error("Error creating commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuters",
                'code' => "R01"
            );
        }
    }


    #[ArrayShape(['time' => "float|int", 'driverHomeToPassengerHomeDistance' => "float|int", 'passengerWorkToDriverDistance' => "float|int", 'driverHomeToPassengerHomeTime' => "float|int", 'passengerWorkToDriverTime' => "float|int", 'distance' => "float|int", 'map_link' => "string"])]
    function calculateTravelTime(CommuterAddress $driverHome, CommuterAddress $passengerHome, CommuterAddress $passengerWork, CommuterAddress $driverWork, $isDriver): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $origin = $driverHome->getLatitude() . "," . $driverHome->getLongitude();
        $destination = $driverWork->getLatitude() . "," . $driverWork->getLongitude();
        $waypoints = $passengerHome->getLatitude() . "," . $passengerHome->getLongitude() . "|" . $passengerWork->getLatitude() . "," . $passengerWork->getLongitude();
        $mapLink = 'https://www.google.com/maps/dir/' . $origin . '/' . $passengerHome->getLatitude() . "," . $passengerHome->getLongitude() . '/' . $passengerWork->getLatitude() . "," . $passengerWork->getLongitude() . '/' . $destination;


        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $origin . "&destination=" . $destination . "&waypoints=" . $waypoints . "&key=" . $_ENV['GOOGLE_API_KEY'] . "&travelMode=driving";
        $this->logger->debug("google api url: " . $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseData = curl_exec($ch);
        curl_close($ch);

        $this->logger->debug("response: " . $responseData);

        $response_a = json_decode($responseData, true);
        // Extract the "legs" array from the response

// Loop through each step in the legs
        $legs = $response_a['routes'][0]['legs'];
        $totalTravelTimeMinutes = 0;
        $totalTravelDistance = 0;
        foreach ($legs as $leg) {
            $distance = $leg['distance']['value'];
            $duration = $leg['duration']['value'];
            $totalTravelTimeMinutes += $duration;
            $totalTravelDistance += $distance;
        }

        return array(
            'time' => $totalTravelTimeMinutes / 60,
            'driverHomeToPassengerHomeDistance' => $legs[0]['distance']['value'] / 1000,
            'passengerWorkToDriverDistance' => $legs[2]['distance']['value'] / 1000,
            'driverHomeToPassengerHomeTime' => $legs[0]['duration']['value'] / 60,
            'passengerWorkToDriverTime' => $legs[2]['duration']['value'] / 60,
            'distance' => $totalTravelDistance / 1000,
            'map_link' => $mapLink);
    }


    public function getAllMatches($driverId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {

            if($driverId === '0'){
                $matches = $this->em->getRepository("App\Entity\CommuterMatch")->createQueryBuilder('c')
                    ->where('c.status = :status')
                    ->andWhere('c.additionalTime < :max_time')
                    ->orderBy('c.additionalTime', 'ASC')
                    ->setParameter('status', "active")
                    ->setParameter('max_time', $_ENV['MAX_ADDITIONAL_TIME'])
                    ->getQuery()
                    ->getResult();
            }else{
                $driver = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => intval($driverId)));
                $matches = $this->em->getRepository("App\Entity\CommuterMatch")->createQueryBuilder('c')
                    ->where('c.status = :status')
                    ->andWhere('c.additionalTime < :max_time')
                    ->andWhere('c.driver = :driverID')
                    ->orderBy('c.additionalTime', 'ASC')
                    ->setParameter('status', "active")
                    ->setParameter('max_time', $_ENV['MAX_ADDITIONAL_TIME'])
                    ->setParameter('driverID', $driver->getId())
                    ->getQuery()
                    ->getResult();
            }


            if (sizeof($matches) == 0) {
                return array(
                    'message' => "No matches found",
                    'code' => "R01"
                );
            }

            $serializer = SerializerBuilder::create()->build();
            $jsonContent = $serializer->serialize($matches, 'json');

            return array(
                'message' => "commuters found",
                'code' => "R00",
                'matches' => $jsonContent
            );
        } catch (\Exception $e) {
            $this->logger->error("Error finding matches " . $e->getMessage());
            return array(
                'message' => "Error getting matches",
                'code' => "R01"
            );
        }
    }

    public function getMatch($id): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {


            $match = $this->em->getRepository(CommuterMatch::class)->findOneBy(array('id' => intval($id)));

            if ($match == null) {
                return array(
                    'message' => "Match found",
                    'code' => "R01"
                );
            }

            $serializer = SerializerBuilder::create()->build();
            $jsonContent = $serializer->serialize($match, 'json');

            return array(
                'message' => "commuter found",
                'code' => "R00",
                'match' => $jsonContent
            );
        } catch (\Exception $e) {
            $this->logger->error("Error finding match " . $e->getMessage());
            return array(
                'message' => "Error getting match",
                'code' => "R01"
            );
        }
    }

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function updateMatchStatus($request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);

            $match = $this->em->getRepository(CommuterMatch::class)->findOneBy(array('id' => intval($parameters["id"])));

            if ($match == null) {
                return array(
                    'message' => "Match found",
                    'code' => "R01"
                );
            }

            if($parameters["commuter_type"] == "driver"){
                $match->setDriverStatus($parameters["status"]);
            }else if($parameters["commuter_type"] == "passenger"){
                $match->setPassengerStatus($parameters["status"]);
            }else {
                $match->setStatus($parameters["status"]);
            }

            //flush
            $this->em->persist($match);
            $this->em->flush();

            return array(
                'message' => "Status updated",
                'code' => "R00"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error finding match " . $e->getMessage());
            return array(
                'message' => "Error getting match",
                'code' => "R01"
            );
        }
    }

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function matchAllDrivers(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            //find all drivers
            $driverCommuters = $this->em->getRepository(Commuter::class)->findBy(array('type' => "driver", 'status' => "active"));
            foreach ($driverCommuters as $driver) {
                $this->logger->info("driver found: " . $driver->getId());
                $driver->setLastMatch(new \DateTime());
                //flush driver
                $this->em->persist($driver);
                $this->em->flush();

                $passengerCommuters = $this->em->getRepository(Commuter::class)->findBy(array('status' => "active", 'type' => "passenger"));
                if (sizeof($passengerCommuters) == 0) {
                    continue;
                }

                foreach ($passengerCommuters as $passenger) {
                    $this->logger->info("commuter found: " . $passenger->getId());

                    //if driver and passenger states are not the same then skip
                    if ($driver->getHomeAddress()->getState() != $passenger->getHomeAddress()->getState()) {
                        $this->logger->info("State not the same");
                        continue;
                    }

                    $passenger->setLastMatch(new \DateTime());
                    //flush driver
                    $this->em->persist($passenger);
                    $this->em->flush();
                    //check

                    //check that the commuter is not matched
                    $matches = $this->em->getRepository("App\Entity\CommuterMatch")->createQueryBuilder('c')
                        ->where('c.driver = :driverId')
                        ->andWhere('c.passenger = :passengerId')
                        ->orderBy('c.additionalTime', 'ASC')
                        ->setParameter('driverId', $driver->getId())
                        ->setParameter('passengerId', $passenger->getId())
                        ->getQuery()
                        ->getResult();

                    if (sizeof($matches) > 0) {
                        $this->logger->info("Match found - " . $passenger->getName() . " - " . $driver->getName());
                        continue;
                    }

                    $travelTimeResponse = $this->calculateTravelTime($driver->getHomeAddress(), $passenger->getHomeAddress(), $passenger->getWorkAddress(), $driver->getWorkAddress(), $driver->getType() == "driver");

                    //write to database
                    $commuterMatch = new CommuterMatch();
                    $commuterMatch->setDriver($driver);
                    $commuterMatch->setPassenger($passenger);
                    $commuterMatch->setTotalTrip($travelTimeResponse["time"]);
                    $commuterMatch->setDistanceHome($travelTimeResponse["driverHomeToPassengerHomeDistance"]);
                    $commuterMatch->setDistanceWork($travelTimeResponse["passengerWorkToDriverDistance"]);
                    $commuterMatch->setDurationHome($travelTimeResponse["driverHomeToPassengerHomeTime"]);
                    $commuterMatch->setDurationWork($travelTimeResponse["passengerWorkToDriverTime"]);

                    $driverTravelTime = $driver->getTravelTime();

                    $commuterMatch->setAdditionalTime(intval($travelTimeResponse["time"] - $driverTravelTime));
                    $commuterMatch->setStatus("active");
                    $commuterMatch->setDriverStatus("pending");
                    $commuterMatch->setPassengerStatus("pending");
                    $commuterMatch->setMapLink($travelTimeResponse["map_link"]);
                    $this->em->persist($commuterMatch);
                    $this->em->flush();
                }
            }

            return array(
                'message' => "Successfully matched commuters",
                'code' => "R00"
            );


        } catch (\Exception $e) {
            $this->logger->error("Error matching commuters " . $e->getMessage());
            return array(
                'message' => "Error matching commuters",
                'code' => "R01"
            );
        }
    }
}