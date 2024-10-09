<?php

namespace App\Controller;


use App\Service\CommuterApi;
use App\Service\ExpenseApi;
use App\Service\MatchService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommuterController extends AbstractController
{


    /**
     * @Route("api/whatsapp/login", methods={"POST"})
     */
    public function loginWithWhatsApp(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $commuterApi->loginWithWhatsApp($request);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/whatsapp/validate", methods={"POST"})
     */
    public function validateVerificationCode(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->validateVerificationCode($request);
        return new JsonResponse($response, 200, array());
    }


    /**
     * @Route("api/commuter/create", methods={"POST"})
     */
    public function createCommuter(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->createCommuter($request);

        if ($response["code"] == "R01") {
            return new JsonResponse($response, 200, array());
        } else {
            return new JsonResponse($response, 201, array());
        }
    }

    /**
     * @Route("api/commuters/{type}", methods={"GET"})
     */
    public function getCommuters($type, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->getAllCommuters($type);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/newdrivers", methods={"GET"})
     */
    public function getDriversWithNoTravelTime(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->getDriversWithNoTravelTime();
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/update/commuter/traveltime", methods={"PUT"})
     */
    public function updateDriverTravelTime(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->updateDriverTravelTime($request);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/update/commuter/status", methods={"PUT"})
     */
    public function updateDriverStatus(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->updateCommuterStatus($request);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/remove/broken", methods={"DELETE"})
     */
    public function removeBrokenStatus(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->removeBrokenStatus();
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/update/commuter/phone", methods={"PUT"})
     */
    public function updateCommuterPhone(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->updateCommuterPhone($request);
        return new JsonResponse($response, 200, array());
    }

}