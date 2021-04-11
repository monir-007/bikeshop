<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    /**
     * @Route("/index")
     */
    public function index():Response
    {
       return $this->render('index.html.twig',[
           'day'=>date('F j, Y, g:i a')
       ]);

    }

}