<?php

namespace App\Form;

use App\DataProvider\MtgDataProvider;
use App\Entity\CollectedCard;
use App\Entity\User;
use App\Helper\LanguageMapper;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CollectedCardType extends AbstractType
{
    public function __construct(
        private readonly MtgDataProvider $mtgDataProvider,
        private readonly LanguageMapper $languageMapper,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CollectedCard $collectedCard */
        $collectedCard = $options['collected_card'];
        $sets = $this->mtgDataProvider->getSets();
        $languageList = $this->languageMapper->getMapping();

        $prints = $options['printings'];

        $editions = [];
        foreach ($prints as $print) {
            $label = $sets[$print['setCode']]->getName() . ' (' . strtoupper($print['setCode']) . ')' . ' (' . $print['number'] . ')';
            $editions[$label] = $print['setCode'] . '-' . $print['number'];
        }

        $languages = [
            'form.card_edit.languages.available'   => [],
            'form.card_edit.languages.unavailable' => [],
        ];

        foreach ($collectedCard->getCard()->getCardLanguages() as $lang) {
            $languages['form.card_edit.languages.available']['language.' . $lang] = $lang;
        }

        foreach ($languageList as $lang) {
            $languages['form.card_edit.languages.unavailable']['language.' . $lang] = $lang;
        }

        $languages['form.card_edit.languages.unavailable'] = array_diff_assoc($languages['form.card_edit.languages.unavailable'], $languages['form.card_edit.languages.available']);

        $builder
            ->add(
                'editionAndSetCode',
                ChoiceType::class,
                [
                    'choices'                   => $editions,
                    'label'                     => 'form.card_edit.edition',
                    'choice_translation_domain' => false,
                    'mapped'                    => false,
                ]
            )
            ->add(
                'language',
                ChoiceType::class,
                [
                    'choices' => $languages,
                    'label'   => 'form.card_edit.language',
                ]
            )
            ->add(
                'nonFoilQuantity',
                IntegerType::class,
                [
                    'label' => 'form.card_edit.non_foil_quantity',
                    'attr'  => [
                        'min' => 0,
                    ],
                ]
            )
            ->add(
                'foilQuantity',
                IntegerType::class,
                [
                    'label' => 'form.card_edit.foil_quantity',
                    'attr'  => [
                        'min' => 0,
                    ],
                ]
            )
            ->add(
                'user',
                EntityType::class,
                [
                    'label'         => 'form.card_edit.owner',
                    'class'         => User::class,
                    'query_builder' => function (UserRepository $repo) {
                        return $repo->getFollowingAndMyselfQueryBuilder($this->tokenStorage->getToken()->getUser());
                    },
                    'choice_label' => 'username',
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.card_edit.save',
                    'attr'  => [
                        'class' => 'btn btn-primary',
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'collected_card' => new CollectedCard(),
            'printings'      => [],
            'data_class'     => CollectedCard::class,
            'attr'           => [
                'autocomplete' => 'off',
            ],
        ]);
    }
}
