<?php

namespace App\Form;

use App\Entity\UserSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                UserSettings::CAN_SEE_COLLECTION,
                ChoiceType::class,
                [
                    'label' => 'form.account.user_settings.can_see_collection',
                    'attr'     => [
                        'placeholder' => 'form.account.user_settings.can_see_collection',
                    ],
                    'choices' => [
                        'form.account.user_settings.choices.nobody'              => UserSettings::NOBODY,
                        'form.account.user_settings.choices.followers'           => UserSettings::FOLLOWERS,
                        'form.account.user_settings.choices.following_followers' => UserSettings::FOLLOWING_FOLLOWERS,
                        'form.account.user_settings.choices.everybody'           => UserSettings::EVERYBODY,
                    ]
                ]
            )
            ->add(
                UserSettings::CAN_SEARCH_IN_COLLECTION,
                ChoiceType::class,
                [
                    'label' => 'form.account.user_settings.can_search_in_collection',
                    'attr'     => [
                        'placeholder' => 'form.account.user_settings.can_search_in_collection',
                    ],
                    'choices' => [
                        'form.account.user_settings.choices.nobody'              => UserSettings::NOBODY,
                        'form.account.user_settings.choices.followers'           => UserSettings::FOLLOWERS,
                        'form.account.user_settings.choices.following_followers' => UserSettings::FOLLOWING_FOLLOWERS,
                    ]
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.account.user_settings.save',
                ]
            )
        ;
    }
}
