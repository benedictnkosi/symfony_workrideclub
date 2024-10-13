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


    public function registerWithWhatsApp(Request $request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);
            $existingCommuter = $this->em->getRepository(Commuter::class)->findOneBy(array('phone' => $parameters["phone"]));
            $verificationCode = rand(100000, 999999);
            $verificationExpires = new \DateTime('+5 minutes');


            if ($existingCommuter !== null) {
                return array(
                    'message' => "User with this number already exists, login instead",
                    'code' => "R01"
                );
            } else {
                $commuter = new Commuter();

                $commuter->setVerificationCode($verificationCode);
                $commuter->setVerificationCodeExpiry($verificationExpires);
                $commuter->setGuid(bin2hex(random_bytes(16)));

                $homeAddressJson = $parameters["home_address"];
                $workAddressJson = $parameters["work_address"];

                if ($homeAddressJson["full_address"] == $workAddressJson["full_address"]) {
                    return array(
                        'message' => "Home and work address cannot be the same",
                        'code' => "R01"
                    );
                }

                $homeAddress = new CommuterAddress();
                $homeAddress->setFullAddress($homeAddressJson["full_address"]);
                $homeAddress->setCity($homeAddressJson["city"]);
                $homeAddress->setState($homeAddressJson["state"]);
                $homeAddress->setLatitude($homeAddressJson["latitude"]);
                $homeAddress->setLongitude($homeAddressJson["longitude"]);
                $homeAddress->setType("home");
                $homeAddress->SetCountry($homeAddressJson["country"]);
                $this->em->persist($homeAddress);
                $this->em->flush();

                $this->logger->info("Home address created " . $homeAddress->getId());


                $workAddress = new CommuterAddress();
                $workAddress->setFullAddress($workAddressJson["full_address"]);
                $homeAddress->setState($workAddressJson["state"]);
                $workAddress->setCity($workAddressJson["city"]);
                $workAddress->setLatitude($workAddressJson["latitude"]);
                $workAddress->setLongitude($workAddressJson["longitude"]);
                $workAddress->setType("work");
                $workAddress->SetCountry($workAddressJson["country"]);

                $this->em->persist($workAddress);
                $this->em->flush();

                $this->logger->info("Work address created " . $workAddress->getId());

                //get driver travel time


                $commuter->setName($parameters["name"]);

                $commuter->setPhone($parameters["phone"]);

                $commuter->setCreated(new \DateTime());
                $commuter->setHomeAddress($homeAddress);
                $commuter->setWorkAddress($workAddress);
                $commuter->setStatus($parameters["status"]);
                $commuter->setType($parameters["type"]);
                $commuter->setTravelTime(0);
                $commuter->setWorkDeparture($parameters["work_departure_time"]);
                $commuter->setHomeDeparture($parameters["home_departure_time"]);
                $commuter->setFuel(0);
                $this->em->persist($commuter);
                $this->em->flush();

                $this->em->persist($commuter);
                $this->em->flush();
            }

            $response = $this->sendVerificationCode($parameters["phone"], $verificationCode);
            $responseData = json_decode($response, true);
            if (!isset($responseData['idMessage'])) {
                return array(
                    'message' => "Failed to send verificatiion code",
                    'code' => "R01"
                );
            } else {
                return array(
                    'message' => "Verification code sent successfully",
                    'code' => "R00"
                );
            }
        } catch (\Exception $e) {
            $this->logger->error("Error generating verification code  " . $e->getMessage());
            return array(
                'message' => "Error sending verification code",
                'code' => "R01"
            );
        }
    }


    public function loginWithWhatsApp($phoneNumber): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('phone' => $phoneNumber));
            $verificationCode = rand(100000, 999999);
            $verificationExpires = new \DateTime('+5 minutes');
            if ($commuter == null) {
                return array(
                    'message' => "User with this number not found, register instead",
                    'code' => "R01"
                );
            } else {

                $commuter->setVerificationCode($verificationCode);
                $commuter->setVerificationCodeExpiry($verificationExpires);
                $commuter->setGuid(bin2hex(random_bytes(16)));

                $this->em->persist($commuter);
                $this->em->flush();
            }

            $response = $this->sendVerificationCode($phoneNumber, $verificationCode);
            $responseData = json_decode($response, true);
            if (!isset($responseData['idMessage'])) {
                return array(
                    'message' => "Failed to send verificatiion code",
                    'code' => "R01"
                );
            } else {
                return array(
                    'message' => "Verification code sent successfully",
                    'code' => "R00"
                );
            }
        } catch (\Exception $e) {
            $this->logger->error("Error generating verification code  " . $e->getMessage());
            return array(
                'message' => "Error sending verification code",
                'code' => "R01"
            );
        }
    }

    public function validateVerificationCode(Request $request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);
            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('phone' => $parameters["phone"]));

            if ($commuter === null) {
                return array(
                    'message' => "Commuter not found",
                    'code' => "R01"
                );
            }

            if ($commuter->getVerificationCode() === $parameters["verification_code"] && $commuter->getVerificationCodeExpiry() > new \DateTime()) {
                $profieComplete = $commuter->getName() !== null;
                return array(
                    'message' => "Verification code is correct",
                    'code' => "R00",
                    'guid' => $commuter->getGuid(),
                    'profile_complete' => $profieComplete
                );
            } else {
                $verificationCode = rand(100000, 999999);
                $commuter->setVerificationCode($verificationCode);
                $commuter->setVerificationCodeExpiry(new \DateTime('+5 minutes'));
                $this->em->persist($commuter);
                $this->em->flush();

                $this->sendVerificationCode($parameters["phone"], $verificationCode);


                return array(
                    'message' => "Verification code is incorrect or expired. A new code has been generated",
                    'code' => "R01"
                );
            }
        } catch (\Exception $e) {
            $this->logger->error("Error validating verification code " . $e->getMessage());
            return array(
                'message' => "Error validating verification code",
                'code' => "R01"
            );
        }
    }

    public function sendVerificationCode($phone, $verificationCode)
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $secret = $_ENV['WHATSAPP_SECRET'];
            $instance = $_ENV['WHATSAPP_INSTANCE'];
            $url = 'https://7103.api.greenapi.com/waInstance' . $instance . '/sendMessage/' . $secret;
            $this->logger->info($url);
            $phone = preg_replace('/^0/', '27', $phone);
            $data = array(
                'chatId' => str_replace('+', '', $phone) . '@c.us',
                'message' => 'Your verification code is: ' . $verificationCode
            );

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);

            if ($response === false) {
                throw new \Exception("Failed to send message: " . curl_error($ch));
            }

            $this->logger->info("Response: " . $response);

            curl_close($ch);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error("Error sending verification code " . $e->getMessage());
            return array(
                'message' => "Error sending verification code",
                'code' => "R01"
            );
        }
    }


    public function updateCommuter(Request $request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);

            $this->logger->info("name: " . $parameters["name"]);

            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('guid' => $parameters["guid"]));
            if ($commuter == null) {
                return array(
                    'message' => "User not found, please logout and login",
                    'code' => "R01"
                );
            }

            $homeAddressJson = $parameters["home_address"];
            $homeAddress = new CommuterAddress();
            $homeAddress->setFullAddress($homeAddressJson["full_address"]);
            $homeAddress->setCity($homeAddressJson["city"]);
            $homeAddress->setState($homeAddressJson["state"]);
            $homeAddress->setLatitude($homeAddressJson["latitude"]);
            $homeAddress->setLongitude($homeAddressJson["longitude"]);
            $homeAddress->setType("home");
            $homeAddress->SetCountry($homeAddressJson["country"]);
            $this->em->persist($homeAddress);
            $this->em->flush();

            $this->logger->info("Home address created " . $homeAddress->getId());

            $workAddressJson = $parameters["work_address"];
            $workAddress = new CommuterAddress();
            $workAddress->setFullAddress($workAddressJson["full_address"]);
            $homeAddress->setState($workAddressJson["state"]);
            $workAddress->setCity($workAddressJson["city"]);
            $workAddress->setLatitude($workAddressJson["latitude"]);
            $workAddress->setLongitude($workAddressJson["longitude"]);
            $workAddress->setType("work");
            $workAddress->SetCountry($workAddressJson["country"]);

            $this->em->persist($workAddress);
            $this->em->flush();

            $this->logger->info("Work address created " . $workAddress->getId());

            //get driver travel time


            $commuter->setName($parameters["name"]);

            $commuter->setPhone($parameters["phone"]);

            $commuter->setCreated(new \DateTime());
            $commuter->setHomeAddress($homeAddress);
            $commuter->setWorkAddress($workAddress);
            $commuter->setStatus($parameters["status"]);
            $commuter->setType($parameters["type"]);
            $commuter->setTravelTime(0);
            $commuter->setWorkDeparture($parameters["work_departure_time"]);
            $commuter->setHomeDeparture($parameters["home_departure_time"]);
            $commuter->setFuel(0);
            $this->em->persist($commuter);
            $this->em->flush();

            $this->logger->info("Commuter updated " . $commuter->getId());

            return array(
                'message' => "Commuter updated successfully",
                'code' => "R00"
            );
        } catch (\Exception $e) {
            $this->logger->error("Error updating commuter " . $e->getMessage());
            return array(
                'message' => "Error updating commuter",
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


    public function getCommuter($guid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $commuter = $this->em->getRepository(Commuter::class)->findOneBy(array('guid' => $guid));
            if (!$commuter) {
                return array(
                    'message' => "No commuters found",
                    'code' => "R01"
                );
            }

            $serializer = SerializerBuilder::create()->build();
            $jsonContent = $serializer->serialize($commuter, 'json');

            return array(
                'message' => "commuter found",
                'code' => "R00",
                'commuter' => $jsonContent
            );
        } catch (\Exception $e) {
            $this->logger->error("Error getting commuter " . $e->getMessage());
            return array(
                'message' => "Error getting commuter",
                'code' => "R01"
            );
        }
    }

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


    public function removeBrokenStatus(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {

            $commuters = $this->em->getRepository(Commuter::class)->findBy(array('status' => 'broken_address'));

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
                'message' => "Driver travel time updated",
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