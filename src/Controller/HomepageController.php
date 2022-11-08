<?php

namespace App\Controller;

use App\DataProvider\MtgDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route(path: '/', name: 'homepage', methods: ['GET'])]
    public function __invoke(MtgDataProvider $scryfallProvider): Response
    {
        return $this->render('home.html.twig');
    }
}
