<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestVueController extends AbstractController
{
    /**
     * @Route("/vue", name="test_vue")
     */
    public function index(): Response
    {
        return $this->render('test_vue/index.html.twig', [
            'controller_name' => 'TestVueController',
        ]);
    }
}
