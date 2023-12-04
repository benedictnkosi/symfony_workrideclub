<?php

namespace App\Controller;


use App\Service\CommuterApi;
use App\Service\MatchService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatchController extends AbstractController
{

    /**
     * @Route("api/tomatch")
     */
    public function getToMatch(Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->getAllUnmatched();
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/savematch")
     */
    public function saveMatch(Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->writeMatchToDB($request);

        if($response["code"] == "R01"){
            return new JsonResponse($response, 200, array());
        }else{
            return new JsonResponse($response, 201, array());
        }
    }


    /**
     * @Route("api/matches/{driverId}/{status}/{time}")
     */
    public function getMatches($driverId,$status,$time, Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->getAllMatches($driverId, $status, $time);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/match/{id}")
     */
    public function getMatch($id, Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->getMatch($id);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/update/match")
     */
    public function updateMatchStatus(Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('PUT')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->updateMatchStatus($request);
        return new JsonResponse($response, 200, array());
    }


    /**
     * @Route("api/commuters/match/{id}")
     */
    public function matchCommuter($id, Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->matchCommuter($id);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/drivers/matchall")
     */
    public function matchAllDrivers( Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->matchAllDrivers();
        return new JsonResponse($response, 200, array());
    }


    /**
     * @Route("api/commuters/unmatch/{id}")
     */
    public function unMatchCommuter($id, Request $request, LoggerInterface $logger, MatchService $matchApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $matchApi->unmatchCommuter($id);
        return new JsonResponse($response, 200, array());
    }

}