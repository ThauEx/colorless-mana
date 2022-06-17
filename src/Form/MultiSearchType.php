<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MultiSearchType extends AbstractType
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'cards',
                TextareaType::class,
                [
                    'required' => true,
                    'label'    => 'form.cards.multi_search.cards',
                    'attr'     => [
                        'placeholder' => 'form.cards.multi_search.cards_placeholder',
                        'rows'        => 5,
                    ],
//                    'sanitize_html' => true,
                ]
            )
           ->add(
                'users',
                EntityType::class,
                [
                    'label'         => 'form.cards.multi_search.users',
                    'class'         => User::class,
                    'query_builder' => function (UserRepository $repo) {
                        return $repo->getFollowingAndMyselfQueryBuilder($this->tokenStorage->getToken()->getUser());
                    },
                    'choice_label' => 'username',
                    'multiple'     => true,
                    'attr'         => [
                        'class' => 'js-select js-select-users',
                    ],
                    'constraints'  => [
                        new Assert\Count(min: 1),
                    ],
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.card_search.name_submit',
                ]
            )
        ;
    }
}
