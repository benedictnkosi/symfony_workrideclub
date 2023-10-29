<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class NavigationController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render("index.html");
    }

    /**
     * @Route("/signup", name="signup")
     */
    public function signup(LoggerInterface $logger): Response
    {
        return $this->render('signup.html');
    }


    /**
     * @Route("/thank-you", name="thank-you")
     */
    public function thank_you(LoggerInterface $logger): Response
    {
        return $this->render('thank-you.html');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function admin(LoggerInterface $logger): Response
    {
        return $this->render('admin_drivers.html');
    }

}
