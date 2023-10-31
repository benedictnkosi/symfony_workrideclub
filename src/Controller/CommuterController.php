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
     * @Route("api/update/driver/status")
     */
    public function updateDriverStatus(Request $request, LoggerInterface $logger, CommuterApi $commuterApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('PUT')) {
            $response = array(
                'message' => "Method Not Allowed",
                'code' => "R01"
            );
            return new JsonResponse($response, 405, array());
        }

        $response = $commuterApi->updateDriverStatus($request);
        return new JsonResponse($response, 200, array());
    }
}