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
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $commuterApi->createCommuter($request);

        if($response["code"] == "R01"){
            return new JsonResponse($response, 200, array());
        }else{
            return new JsonResponse($response, 201, array());
        }
    }

}