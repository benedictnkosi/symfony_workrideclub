<?php

namespace App\Service;

use App\Entity\Commuter;
use App\Entity\CommuterAddress;
use App\Entity\CommuterMatch;
use Cassandra\Date;
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

            $existingCommuter = $this->em->getRepository(Commuter::class)->findOneBy(array('phone' => $parameters["phone"]));
            if ($existingCommuter !== null) {
                return array(
                    'message' => "Phone number already exists",
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
            $homeAddress->setState($parameters["home_address_state"]);
            $workAddress->setCity($parameters["work_city"]);
            $workAddress->setLatitude($parameters["work_address_lat"]);
            $workAddress->setLongitude($parameters["work_address_long"]);
            $workAddress->setType("work");
            $workAddress->SetCountry($parameters["country"]);

            $this->em->persist($workAddress);
            $this->em->flush();

            $this->logger->info("Work address created " . $workAddress->getId());

            //get driver travel time
            //$travelTime = $this->calculateDriverTravelTime($parameters["home_address_lat"], $parameters["home_address_long"], $parameters["work_address_lat"], $parameters["work_address_long"]);

            $commuter = new Commuter();
            $commuter->setName($parameters["name"]);

            $commuter->setPhone($parameters["phone"]);

            $commuter->setCreated(new \DateTime());
            $commuter->setHomeAddress($homeAddress);
            $commuter->setWorkAddress($workAddress);
            $commuter->setStatus("active");
            $commuter->setType($parameters["type"]);
            $commuter->setTravelTime(0);
            $commuter->setWorkDeparture($parameters["work_departure_time"]);
            $commuter->setHomeDeparture($parameters["home_departure_time"]);
            $commuter->setFuel($parameters["fuel_contribution"]);
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
            if ($type == "all") {
                $commuters = $this->em->getRepository(Commuter::class)->findBy(array('status' => 'active'));
            } else {
                $commuters = $this->em->getRepository(Commuter::class)->findBy(array('type' => $type), array('created' => 'DESC'));

            }
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

    #[ArrayShape(['time' => "int", 'distance' => "int"])]
    function calculateDriverTravelTime($homeLat, $homeLong, $workLat, $workLong): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $origin = $homeLat . "," . $homeLong;
        $destination = $workLat . "," . $workLong;

        $url = "https://www.google.com/maps/dir/-26.157075,27.864538/-26.2483676,27.9498404/-26.2933531,28.1102412/-26.2739937,28.125954/@-26.2198705,27.8304349,11z?entry=ttu";
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

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function updateCommuterPhone($request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);

            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => intval($parameters["id"])));

            if ($commuter == null) {
                return array(
                    'message' => "Commuter not found",
                    'code' => "R01"
                );
            }

            $commuter->setPhone($parameters["phone"]);
            $this->em->persist($commuter);
            $this->em->flush();

            return array(
                'message' => "Phone updated",
                'code' => "R00"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error finding commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuter",
                'code' => "R01"
            );
        }
    }

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function updateCommuterStatus($request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);

            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => intval($parameters["id"])));

            if ($commuter == null) {
                return array(
                    'message' => "Commuter not found",
                    'code' => "R01"
                );
            }

            if ($parameters["status"] == "deleted") {
                //remove all matches
                if ($commuter->getType() == "driver") {
                    $matches = $this->em->getRepository(CommuterMatch::class)->findBy(array('driver' => $commuter));
                    foreach ($matches as $match) {
                        $this->em->remove($match);
                    }
                } else {
                    $matches = $this->em->getRepository(CommuterMatch::class)->findBy(array('passenger' => $commuter));
                    foreach ($matches as $match) {
                        $this->em->remove($match);
                    }
                }
                //flush
                $commuter->setStatus($parameters["status"]);
                $this->em->persist($commuter);
            } else {
                $commuter->setStatus($parameters["status"]);
                $this->em->persist($commuter);
            }
            $this->em->flush();

            return array(
                'message' => "Status updated",
                'code' => "R00"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error finding commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuter",
                'code' => "R01"
            );
        }
    }


    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function removeBrokenStatus(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {

            $commuters = $this->em->getRepository(Commuter::class)->findOneBy(array('status' => 'broken'));

            if (sizeof($commuters) < 1) {
                return array(
                    'message' => "Commuters not found",
                    'code' => "R01"
                );
            }

           foreach ($commuters as $commuter) {
               //remove all matches
               $commuter->setStatus("active");
               $this->em->persist($commuter);
           }

            $this->em->flush();

            return array(
                'message' => "Status updated",
                'code' => "R00"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error finding commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuter",
                'code' => "R01"
            );
        }
    }

    public function getJoinedLastDays($type, $days): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            //create date object for $days ago at midnight
            $date = new \DateTime();
            $date->modify('-' . $days . ' day');
            $date->setTime(0, 0, 0);

            $commuters = $this->em->getRepository("App\Entity\Commuter")->createQueryBuilder('c')
                ->where('c.created > :date')
                ->andWhere('c.type = :type')
                ->setParameter('date', $date)
                ->setParameter('type', $type)
                ->getQuery()
                ->getResult();
            return array(
                'count' => sizeof($commuters),
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

    public function getFBJoinedLastDays($type, $days): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            //create date object for $days ago at midnight
            $date = new \DateTime();
            $date->modify('-' . $days . ' day');
            $date->setTime(0, 0, 0);

            //and phone number contains facebook string (fb)
            $commuters = $this->em->getRepository("App\Entity\Commuter")->createQueryBuilder('c')
                ->where('c.created > :date')
                ->andWhere('c.type = :type')
                ->andWhere('c.phone LIKE :phone')
                ->setParameter('date', $date)
                ->setParameter('type', $type)
                ->setParameter('phone', '%facebook%')
                ->getQuery()
                ->getResult();
            return array(
                'count' => sizeof($commuters),
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

    public function getRegistrationStats(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();


        $sql = "SELECT DATE(created) AS creation_day, COUNT(*) AS created_commuters
FROM commuter
GROUP BY creation_day;";
        $this->logger->info("sql " . $sql);

        $databaseHelper = new DatabaseApi($this->logger);
        $result = $databaseHelper->queryDatabase($sql);

        if (!$result) {
            return array();
        } else {
            while ($results = $result->fetch_assoc()) {
                $this->logger->info("found " . $results["creation_day"]);
                $responseArray[] = array(
                    'day' => str_replace( "2023-", "", $results["creation_day"]),
                    'count' => $results["created_commuters"]
                );
            }
        }
        return $responseArray;
    }

    public function getDriversWithNoTravelTime(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $commuters = $this->em->getRepository(Commuter::class)->findBy(array('type' => 'driver', 'status' => 'active', 'travelTime' => 0), array('created' => 'DESC'));
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

    #[ArrayShape(['message' => "string", 'code' => "string"])]
    public function updateDriverTravelTime($request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);

            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('id' => intval($parameters["id"])));

            if ($commuter == null) {
                return array(
                    'message' => "Commuter not found",
                    'code' => "R01"
                );
            }

            $commuter->setTravelTime($parameters["travel_time"]);
            $this->em->persist($commuter);
            $this->em->flush();

            return array(
                'message' => "Status updated",
                'code' => "R00"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error finding commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuter",
                'code' => "R01"
            );
        }
    }

}