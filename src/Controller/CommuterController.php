<?php

namespace App\Controller;


use App\Service\CommuterApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommuterController extends AbstractController
{
    /**
     * @Route("api/commuter/create")
     */
    public function createCommuter(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->createCommuter($request);

        if($response["code"] == "R01"){
            return new JsonResponse($response, 200, array());
        }else{
            return new JsonResponse($response, 201, array());
        }
    }

    /**
     * @Route("api/commuters/{type}")
     */
    public function getCommuters($type, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->getAllCommuters($type);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/matches/{driverId}")
     */
    public function getMatches($driverId, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->getAllMatches($driverId);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/match/{id}")
     */
    public function getMatch($id, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->getMatch($id);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/update/match")
     */
    public function updateMatchStatus(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('PUT')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->updateMatchStatus($request);
        return new JsonResponse($response, 200, array());
    }


    /**
     * @Route("api/commuters/match/{id}")
     */
    public function matchCommuter($id, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->matchCommuter($id);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/commuters/unmatch/{id}")
     */
    public function unMatchCommuter($id, Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->unmatchCommuter($id);
        return new JsonResponse($response, 200, array());
    }

}