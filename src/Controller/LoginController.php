<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    /** @Route("/login", name="login", methods={"GET"}) */
    public function login(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        return $this->render('login.html.twig');
    }

    /** @Route("/logout", name="logout", methods={"GET"}) */
    public function logout(): void
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    /** @Route("/login/discord", name="login_discord_start") */
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('discord')
            ->redirect([
	    	    'identify',
                'email',
            ], [])
        ;
    }

    /** @Route("/login/discord/check", name="login_discord_check") */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry): void
    {
    }
}
