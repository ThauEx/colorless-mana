<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserSettingsType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/account", name="account_") */
class AccountController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_USER')")
     * @Route("/settings", name="settings")
     */
    public function settings(Request $request, ManagerRegistry $doctrine): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserSettingsType::class, $user->getSettings()->getAll());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $user->getSettings()->setAll($form->getData());

            $em->flush();

            $this->addFlash('info', 'account.settings.updated');
        }

        return $this->render('account/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
