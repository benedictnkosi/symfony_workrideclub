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

                //if already matched
                if (sizeof($matches) > 0) {
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
                $commuterMatch->setTotalTrip(intval($travelTimeResponse["time"]));
                $commuterMatch->setDistanceHome(intval($travelTimeResponse["driverHomeToPassengerHomeDistance"]));
                $commuterMatch->setDistanceWork(intval($travelTimeResponse["passengerWorkToDriverDistance"]));
                $commuterMatch->setDurationHome(intval($travelTimeResponse["driverHomeToPassengerHomeTime"]));
                $commuterMatch->setDurationWork(intval($travelTimeResponse["passengerWorkToDriverTime"]));

                if ($currentCommuter->getType() == "passenger") {
                    $driverTravelTime = $commuter->getTravelTime();
                } else {
                    $driverTravelTime = $currentCommuter->getTravelTime();
                }

                $commuterMatch->setAdditionalTime(intval($travelTimeResponse["time"] - $driverTravelTime));
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
    public function writeMatchToDB($request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {

            $parameters = json_decode($request->getContent(), true);

            $this->logger->info("driver: " . $parameters["driver"]);

            $driver = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => intval($parameters["driver"])));
            $passenger = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => intval($parameters["passenger"])));
            if ($driver == null || $passenger == null) {
                return array(
                    'message' => "Driver or passenger not found",
                    'code' => "R01"
                );
            }

            //write to database
            $commuterMatch = new CommuterMatch();
            $commuterMatch->setDriver($driver);
            $commuterMatch->setPassenger($passenger);
            $commuterMatch->setTotalTrip(intval($parameters["totalTrip"]));
            $commuterMatch->setAdditionalKm(0);
            $commuterMatch->setDistanceWork(0);
            $commuterMatch->setDurationHome(0);
            $commuterMatch->setDurationWork(0);

            $commuterMatch->setAdditionalTime(intval($parameters["totalTrip"] - $driver->getTravelTime()));
            $commuterMatch->setStatus("active");
            $commuterMatch->setDriverStatus("pending");
            $commuterMatch->setPassengerStatus("pending");
            $commuterMatch->setMapLink($parameters["mapLink"]);
            $this->em->persist($commuterMatch);
            $this->em->flush();

            return array(
                'message' => "Successfully saved match",
                'code' => "R00"
            );

        } catch (\Exception $e) {
            $this->logger->error("Error saving match " . $e->getMessage());
            return array(
                'message' => "Error saving match",
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
            } else {
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

    function getURL(CommuterAddress $driverHome, CommuterAddress $passengerHome, CommuterAddress $passengerWork, CommuterAddress $driverWork, $isDriver): string
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $origin = $driverHome->getLatitude() . "," . $driverHome->getLongitude();
        $destination = $driverWork->getLatitude() . "," . $driverWork->getLongitude();
        return 'https://www.google.com/maps/dir/' . $origin . '/' . $passengerHome->getLatitude() . "," . $passengerHome->getLongitude() . '/' . $passengerWork->getLatitude() . "," . $passengerWork->getLongitude() . '/' . $destination;
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
            'map_link' => $mapLink
        );
    }

    public function getAllMatches($driverId, $status, $time): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {

            if ($driverId === '0') {
                $matches = $this->em->getRepository("App\Entity\CommuterMatch")->createQueryBuilder('c')
                    ->where('c.status = :status')
                    ->andWhere('c.additionalTime < :max_time')
                    ->orderBy('c.additionalTime', 'ASC')
                    ->setParameter('status', $status)
                    ->setParameter('max_time', $time)
                    ->getQuery()
                    ->getResult();
            } else {
                $matches = $this->em->getRepository("App\Entity\CommuterMatch")->createQueryBuilder('c')
                    ->where('c.status = :status')
                    ->andWhere('c.driver = :driverId')
                    ->orderBy('c.additionalTime', 'ASC')
                    ->setParameter('status', $status)
                    ->setParameter('driverId', $driverId)
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

    public function getUserMathes($uid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {


            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('guid' => $uid));
            if ($commuter == null) {
                return array(
                    'message' => "User not found, please logout and login",
                    'code' => "R01"
                );
            }

            if ($commuter->getType() == "driver") {
                $matches = $this->em->getRepository(CommuterMatch::class)->createQueryBuilder('c')
                    ->where('c.driver = :driverId')
                    ->andWhere('c.additionalTime < :maxTime')
                    ->andWhere('c.status = :status')
                    ->setParameter('driverId', $commuter->getId())
                    ->setParameter('maxTime', 15)
                    ->setParameter('status', "active")
                    ->getQuery()
                    ->getResult();
            } else {
                $matches = $this->em->getRepository(CommuterMatch::class)->createQueryBuilder('c')
                    ->where('c.passenger = :passengerId')
                    ->andWhere('c.additionalTime < :maxTime')
                    ->andWhere('c.status = :status')
                    ->setParameter('passengerId', $commuter->getId())
                    ->setParameter('maxTime', 15)
                    ->setParameter('status', "active")
                    ->getQuery()
                    ->getResult();
            }

            $serializer = SerializerBuilder::create()->build();

            foreach ($matches as $match) {
                $match->getDriver()->setGuid(null);
                $match->getPassenger()->setGuid(null);
                $match->getDriver()->setVerificationCode(null);
                $match->getPassenger()->setVerificationCode(null);
            }

            $jsonContent = $serializer->serialize($matches, 'json');


            return array(
                'message' => "commuter found",
                'code' => "R00",
                'matches' => $jsonContent,
                'user_type' => $commuter->getType()
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

            if ($parameters["commuter_type"] == "driver") {
                $match->setDriverStatus($parameters["status"]);
                if ($parameters["status"] == "rejected") {
                    $match->setStatus("driver_rejected");
                } elseif ($parameters["status"] == "accepted") {
                    if ($match->getPassengerStatus() == "accepted") {
                        $match->setStatus("matched");
                    } else {
                        $match->setStatus("active");
                    }
                }
            } else if ($parameters["commuter_type"] == "passenger") {
                $match->setPassengerStatus($parameters["status"]);
                if ($parameters["status"] == "rejected") {
                    $match->setStatus("passenger_rejected");
                } elseif ($parameters["status"] == "accepted") {
                    if ($match->getDriverStatus() == "accepted") {
                        $match->setStatus("matched");
                    } else {
                        $match->setStatus("active");
                    }
                }
            } else {
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
            // Find all active drivers
            $driverCommuters = $this->em->getRepository(Commuter::class)
                ->findBy(['type' => "driver", 'status' => "active"], ['created' => 'DESC']);

            // Find all active passengers
            $passengerCommuters = $this->em->getRepository(Commuter::class)
                ->findBy(['status' => "active", 'type' => "passenger"]);

            // Initialize an array to store commuter matches
            $matches = [];
            $commuters = [];
            $numberOfMatches = 0;
            foreach ($driverCommuters as $driver) {
                $this->logger->info("Driver found: " . $driver->getId());
                $driver->setLastMatch(new \DateTime());

                foreach ($passengerCommuters as $passenger) {
                    $this->logger->info("Commuter found: " . $passenger->getId());

                    if ($driver->getHomeAddress()->getState() != $passenger->getHomeAddress()->getState()) {
                        $this->logger->info("State not the same " . $driver->getHomeAddress()->getState() . " - " . $passenger->getHomeAddress()->getState());
                        continue;
                    }

                    // Check if the commuter is already matched
                    $isMatched = $this->isMatched($driver->getId(), $passenger->getId());

                    if (!$isMatched) {
                        $passenger->setLastMatch(new \DateTime());
                        $travelTimeResponse = $this->calculateTravelTime(
                            $driver->getHomeAddress(),
                            $passenger->getHomeAddress(),
                            $passenger->getWorkAddress(),
                            $driver->getWorkAddress(),
                            $driver->getType() == "driver"
                        );

                        // Create a commuter match object
                        $commuterMatch = new CommuterMatch();
                        $commuterMatch->setDriver($driver);
                        $commuterMatch->setPassenger($passenger);
                        $commuterMatch->setTotalTrip(intval($travelTimeResponse["time"]));
                        $commuterMatch->setDistanceHome(intval($travelTimeResponse["driverHomeToPassengerHomeDistance"]));
                        $commuterMatch->setDistanceWork(intval($travelTimeResponse["passengerWorkToDriverDistance"]));
                        $commuterMatch->setDurationHome(intval($travelTimeResponse["driverHomeToPassengerHomeTime"]));
                        $commuterMatch->setDurationWork(intval($travelTimeResponse["passengerWorkToDriverTime"]));

                        $driverTravelTime = $driver->getTravelTime();

                        $commuterMatch->setAdditionalTime(intval($travelTimeResponse["time"] - $driverTravelTime));
                        $commuterMatch->setStatus("active");
                        $commuterMatch->setDriverStatus("pending");
                        $commuterMatch->setPassengerStatus("pending");
                        $commuterMatch->setMapLink($travelTimeResponse["map_link"]);


                        // Add to the matches array

                        $numberOfMatches++;

                        $this->logger->info("commuter match saved in DB");

                        $matches[] = $commuterMatch;
                        $commuters[] = $passenger;
                        $commuters[] = $driver;

                        if ($numberOfMatches > 100) {
                            $this->logger->info("100 matches done");
                            break;
                        }
                    } else {
                        $this->logger->info("Match found - " . $passenger->getName() . " - " . $driver->getName());
                    }
                }
            }

            // Batch insert all commuter matches
            foreach ($matches as $match) {
                $this->em->persist($match);
            }

            foreach ($commuters as $commuter) {
                $this->em->persist($commuter);
            }
            $this->em->flush();



            if ($numberOfMatches > 100) {
                $this->logger->info("100 matches done");
                return [
                    'message' => "100 matches done, Please run again to continue",
                    'code' => "R00"
                ];
            } else {
                return [
                    'message' => "Successfully matched commuters",
                    'code' => "R00"
                ];
            }


        } catch (\Exception $e) {
            $this->logger->error("Error matching commuters " . $e->getMessage());
            return [
                'message' => "Error matching commuters",
                'code' => "R01"
            ];
        }
    }

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function getAllUnmatched(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            // Find all active drivers
            $driverCommuters = $this->em->getRepository(Commuter::class)
                ->findBy(['type' => "driver", 'status' => "active"], ['created' => 'DESC']);

            // Find all active passengers
            $passengerCommuters = $this->em->getRepository(Commuter::class)
                ->findBy(['status' => "active", 'type' => "passenger"]);

            // Initialize an array to store commuter matches

            $toMatch = [];
            foreach ($driverCommuters as $driver) {
                $this->logger->info("Driver found: " . $driver->getId());
                $driver->setLastMatch(new \DateTime());

                foreach ($passengerCommuters as $passenger) {
                    $this->logger->info("Commuter found: " . $passenger->getId());

                    if ($driver->getHomeAddress()->getState() != $passenger->getHomeAddress()->getState()) {
                        $this->logger->info("State not the same " . $driver->getHomeAddress()->getState() . " - " . $passenger->getHomeAddress()->getState());
                        continue;
                    }

                    // Check if the commuter is already matched
                    $isMatched = $this->isMatched($driver->getId(), $passenger->getId());

                    if (!$isMatched) {
                        $passenger->setLastMatch(new \DateTime());
                        $url = $this->getURL(
                            $driver->getHomeAddress(),
                            $passenger->getHomeAddress(),
                            $passenger->getWorkAddress(),
                            $driver->getWorkAddress(),
                            $driver->getType() == "driver"
                        );

                        $extraDistance = $this->calculateExtraDistance(
                            $driver->getHomeAddress(),
                            $driver->getWorkAddress(),
                            $passenger->getHomeAddress(),
                            $passenger->getWorkAddress()
                        );

                        if ($extraDistance > 5) {
                            $commuterMatch = new CommuterMatch();
                            $commuterMatch->setDriver($driver);
                            $commuterMatch->setPassenger($passenger);
                            $commuterMatch->setTotalTrip(999);
                            $commuterMatch->setDistanceWork(0);
                            $commuterMatch->setDurationHome(0);
                            $commuterMatch->setDurationWork(0);

                            $commuterMatch->setAdditionalTime(999);
                            $commuterMatch->setStatus("ai_costly");
                            $commuterMatch->setDriverStatus("ai_costly");
                            $commuterMatch->setPassengerStatus("costly");
                            $commuterMatch->setMapLink("");
                            $this->em->persist($commuterMatch);
                            $this->em->flush();

                            $this->logger->info("Match found but extra distance to costly- " . $extraDistance . " - " . $passenger->getName() . " - " . $driver->getName());
                        } else {
                            return [
                                'driver' => $driver->getId(),
                                'passenger' => $passenger->getId(),
                                'url' => $url,
                                'extraDistance' => $extraDistance
                            ];
                        }
                    } else {
                        $this->logger->info("Match found - " . $passenger->getName() . " - " . $driver->getName());
                    }
                }
            }


            return $toMatch;

        } catch (\Exception $e) {
            $this->logger->error("Error matching commuters " . $e->getMessage());
            return [
                'message' => "Error matching commuters" . $e->getMessage(),
                'code' => "R01"
            ];
        }
    }

    public function calculateExtraDistance(CommuterAddress $driverHome, CommuterAddress $driverWork, CommuterAddress $passengerHome, CommuterAddress $passengerWork): float
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        $earthRadius = 6371; // Radius of the earth in km

        // Convert degrees to radians
        $latFrom = deg2rad($driverHome->getLatitude());
        $lonFrom = deg2rad($driverHome->getLongitude());
        $latTo = deg2rad($driverWork->getLatitude());
        $lonTo = deg2rad($driverWork->getLongitude());

        // Haversine formula to calculate the distance between two points
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $directDistance = $earthRadius * $c;

        // Calculate distance with passenger pickup
        $latToPassengerHome = deg2rad($passengerHome->getLatitude());
        $lonToPassengerHome = deg2rad($passengerHome->getLongitude());
        $latToPassengerWork = deg2rad($passengerWork->getLatitude());
        $lonToPassengerWork = deg2rad($passengerWork->getLongitude());

        // Driver home to passenger home
        $latDelta = $latToPassengerHome - $latFrom;
        $lonDelta = $lonToPassengerHome - $lonFrom;
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latToPassengerHome) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $driverToPassengerHomeDistance = $earthRadius * $c;

        // Passenger home to passenger work
        $latDelta = $latToPassengerWork - $latToPassengerHome;
        $lonDelta = $lonToPassengerWork - $lonToPassengerHome;
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latToPassengerHome) * cos($latToPassengerWork) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $passengerHomeToWorkDistance = $earthRadius * $c;

        // Passenger work to driver work
        $latDelta = $latTo - $latToPassengerWork;
        $lonDelta = $lonTo - $lonToPassengerWork;
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latToPassengerWork) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $passengerWorkToDriverWorkDistance = $earthRadius * $c;

        // Total distance with passenger pickup
        $pickupDistance = $driverToPassengerHomeDistance + $passengerHomeToWorkDistance + $passengerWorkToDriverWorkDistance;

        // Calculate extra distance
        $extraDistance = $pickupDistance - $directDistance;

        return $extraDistance;
    }

    private function getGoogleMapsApiResponse(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseData = curl_exec($ch);
        curl_close($ch);

        return json_decode($responseData, true);
    }

    private function extractDistanceFromResponse(array $response): float
    {
        $legs = $response['routes'][0]['legs'];
        $totalDistance = 0;
        foreach ($legs as $leg) {
            $totalDistance += $leg['distance']['value'];
        }
        return $totalDistance;
    }

    public function getMatchedNumber(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            // Find all active drivers
            $commuterMatches = $this->em->getRepository(CommuterMatch::class)
                ->findAll();

            return [
                'message' => "Error matching commuters",
                'code' => "R01",
                'count' => sizeof($commuterMatches)
            ];

        } catch (\Exception $e) {
            $this->logger->error("Error matching commuters " . $e->getMessage());
            return [
                'message' => "Error matching commuters" . $e->getMessage(),
                'code' => "R01"
            ];
        }
    }


    private function isMatched($driverId, $passengerId): bool
    {
        $query = $this->em->getRepository("App\Entity\CommuterMatch")
            ->createQueryBuilder('c')
            ->where('c.driver = :driverId')
            ->andWhere('c.passenger = :passengerId')
            ->setParameter('driverId', $driverId)
            ->setParameter('passengerId', $passengerId)
            ->getQuery();

        return count($query->getResult()) > 0;
    }

}