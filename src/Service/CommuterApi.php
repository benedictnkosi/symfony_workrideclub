<?php

namespace App\Service;

use App\Entity\Commuter;
use App\Entity\CommuterAddress;
use App\Entity\CommuterMatch;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


class CommuterApi extends AbstractController
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function createCommuter(Request $request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);

            $this->logger->info("name: " . $parameters["name"]);

            $existingCommuter = $this->em->getRepository(Commuter::class)->findOneBy(array('email' => $parameters["email"]));
            if ($existingCommuter !== null) {
                return array(
                    'message' => "Email already exists",
                    'code' => "R01"
                );
            }

            $homeAddress = new CommuterAddress();
            $homeAddress->setFullAddress($parameters["home_address"]);
            $homeAddress->setCity($parameters["home_address_city"]);
            $homeAddress->setState($parameters["home_address_state"]);
            $homeAddress->setLatitude($parameters["home_address_lat"]);
            $homeAddress->setLongitude($parameters["home_address_long"]);
            $homeAddress->setType("home");
            $homeAddress->SetCountry($parameters["country"]);
            $this->em->persist($homeAddress);
            $this->em->flush();

            $this->logger->info("Home address created " . $homeAddress->getId());

            $workAddress = new CommuterAddress();
            $workAddress->setFullAddress($parameters["work_address"]);
            $workAddress->setCity($parameters["work_city"]);
            $workAddress->setLatitude($parameters["work_address_lat"]);
            $workAddress->setLongitude($parameters["work_address_long"]);
            $workAddress->setType("work");
            $workAddress->SetCountry($parameters["country"]);

            $this->em->persist($workAddress);
            $this->em->flush();

            $this->logger->info("Work address created " . $workAddress->getId());

            //get driver travel time
            $travelTime = $this->calculateDriverTravelTime($parameters["home_address_lat"], $parameters["home_address_long"], $parameters["work_address_lat"], $parameters["work_address_long"]);

            $commuter = new Commuter();
            $commuter->setName($parameters["name"]);
            $commuter->setEmail($parameters["email"]);
            $commuter->setPhone($parameters["phone"]);
            $commuter->setCreated(new \DateTime());
            $commuter->setHomeAddress($homeAddress);
            $commuter->setWorkAddress($workAddress);
            $commuter->setStatus("active");
            $commuter->setType($parameters["type"]);
            $commuter->setTravelTime($travelTime["time"]);
            $this->em->persist($commuter);
            $this->em->flush();

            $this->logger->info("Commuter created " . $commuter->getId());

            return array(
                'message' => "Commuter created successfully",
                'code' => "R00"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error creating commuter " . $e->getMessage());
            return array(
                'message' => "Error creating commuter",
                'code' => "R01"
            );
        }
    }


    public function getAllCommuters($type): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $commuters = $this->em->getRepository(Commuter::class)->findBy(array('status' => "active", 'type' => $type));
            if (sizeof($commuters) == 0) {
                return array(
                    'message' => "No commuters found",
                    'code' => "R01"
                );
            }

            $serializer = SerializerBuilder::create()->build();
            $jsonContent = $serializer->serialize($commuters, 'json');

            return array(
                'message' => "commuters found",
                'code' => "R00",
                'commuters' => $jsonContent
            );
        } catch (\Exception $e) {
            $this->logger->error("Error creating commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuterS",
                'code' => "R01"
            );
        }
    }

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

                //check that the commuter is not matched
                $matches = $this->em->getRepository("App\Entity\CommuterMatch")->createQueryBuilder('c')
                    ->where('c.driver = :driverId')
                    ->andWhere('c.passenger < :passengerId')
                    ->orderBy('c.additionalTime', 'ASC')
                    ->setParameter('driverId', $currentCommuter->getId())
                    ->setParameter('passengerId', $commuter->getId())
                    ->getQuery()
                    ->getResult();

                if(sizeof($matches) > 0){
                    $this->logger->info("Match found");
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

    function calculateTravelTime(CommuterAddress $driverHome,CommuterAddress $passengerHome,CommuterAddress $passengerWork,CommuterAddress $driverWork, $isDriver): array
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

    function calculateDriverTravelTime($homeLat, $homeLong, $workLat, $workLong): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $origin = $homeLat . "," . $homeLong;
        $destination = $workLat . "," . $workLong;

        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $origin . "&destination=" . $destination . "&key=" . $_ENV['GOOGLE_API_KEY'] . "&travelMode=driving";
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
            'time' => intval($totalTravelTimeMinutes / 60),
            'distance' => intval($totalTravelDistance / 1000)
        );
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


}