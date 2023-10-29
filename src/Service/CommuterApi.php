<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Commuter;
use App\Entity\CommuterAddress;
use App\Entity\Units;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
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

    public function createCommuter(Request $request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $parameters = json_decode($request->getContent(), true);

            $this->logger->info("name: " . $parameters["name"]);

            $existingCommuter = $this->em->getRepository(Commuter::class)->findOneBy(array('email' => $parameters["email"]));
            if($existingCommuter !== null){
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
            $homeAddress->setType("home")   ;
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

            $commuter = new Commuter();
            $commuter->setName($parameters["name"]);
            $commuter->setEmail($parameters["email"]);
            $commuter->setPhone($parameters["phone"]);
            $commuter->setCreated(new \DateTime());
            $commuter->setHomeAddress($homeAddress);
            $commuter->setWorkAddress($workAddress);
            $commuter->setStatus("active");
            $commuter->setType($parameters["type"]);
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
}