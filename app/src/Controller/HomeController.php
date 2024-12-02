<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        
        $appCall = "Unknown application";
        $serverUsed = $request->headers->get('X-Server-Used', 'unknown');

        dd($serverUsed);
        if (strpos($uri, '/app_one') === 0) {
            $appCall = "App One is called";
        } elseif (strpos($uri, '/app_two') === 0) {
            $appCall = "App Two is called";
        }
        return $this->render('home/index.html.twig', [
            'appCall' => $appCall
        ]);
    }
}
