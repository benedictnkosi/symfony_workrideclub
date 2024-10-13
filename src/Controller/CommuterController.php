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
     * @Route("api/whatsapp/register", methods={"POST", "OPTIONS"})
     */
    public function registerWithWhatsApp(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }

        $logger->info("Starting Method: " . __METHOD__);
        $response = $commuterApi->registerWithWhatsApp($request);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    /**
     * @Route("api/whatsapp/sendcode/{phoneNumber}", methods={"GET", "OPTIONS"})
     */
    public function loginWithWhatsApp($phoneNumber, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'GET, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }

        $logger->info("Starting Method: " . __METHOD__);
        $response = $commuterApi->loginWithWhatsApp($phoneNumber);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }


    /**
     * @Route("api/whatsapp/validate", methods={"POST", "OPTIONS"})
     */
    public function validateVerificationCode(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->validateVerificationCode($request);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }


    /**
     * @Route("api/commuter/update", methods={"PUT", "OPTIONS"})
     */
    public function updateCommuter(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'PUT, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->updateCommuter($request);

        if ($response["code"] == "R01") {
            return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
        } else {
            return new JsonResponse($response, 201, array('Access-Control-Allow-Origin' => '*'));
        }
    }

    /**
     * @Route("api/commuter/{guid}", methods={"GET", "OPTIONS"})
     */
    public function getCommuter($guid, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->getCommuter($guid);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    /**
     * @Route("api/commuters/{type}", methods={"GET", "OPTIONS"})
     */
    public function getCommuters($type, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->getAllCommuters($type);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    /**
     * @Route("api/newdrivers", methods={"GET", "OPTIONS"})
     */
    public function getDriversWithNoTravelTime(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->getDriversWithNoTravelTime();
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    /**
     * @Route("api/update/commuter/traveltime", methods={"PUT", "OPTIONS"})
     */
    public function updateDriverTravelTime(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);

        $response = $commuterApi->updateDriverTravelTime($request);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    /**
     * @Route("api/update/commuter/status", methods={"PUT", "OPTIONS"})
     */
    public function updateDriverStatus(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->updateCommuterStatus($request);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    /**
     * @Route("api/remove/broken", methods={"PUT", "OPTIONS"})
     */
    public function removeBrokenStatus(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'PUT, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->removeBrokenStatus();
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

    /**
     * @Route("api/update/commuter/phone", methods={"PUT", "OPTIONS"})
     */
    public function updateCommuterPhone(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        if ($request->getMethod() === "OPTIONS") {
            return new Response('', 200, array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'POST, OPTIONS', 'Access-Control-Allow-Headers' => 'Content-Type'));
        }
        $logger->info("Starting Method: " . __METHOD__);


        $response = $commuterApi->updateCommuterPhone($request);
        return new JsonResponse($response, 200, array('Access-Control-Allow-Origin' => '*'));
    }

}