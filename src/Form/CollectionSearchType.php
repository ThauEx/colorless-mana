<?php

namespace App\Form;

use App\DataProvider\CollectionStatsProvider;
use App\DataProvider\MtgDataProvider;
use App\Doctrine\UuidEncoder;
use App\Entity\User;
use App\Helper\LanguageMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionSearchType extends AbstractType
{
    private MtgDataProvider $mtgDataProvider;
    private CollectionStatsProvider $collectionStatsProvider;
    private LanguageMapper $languageMapper;
    private UuidEncoder $uuidEncoder;

    public function __construct(
        MtgDataProvider $mtgDataProvider,
        CollectionStatsProvider $collectionStatsProvider,
        LanguageMapper $languageMapper,
        UuidEncoder $uuidEncoder
    ) {
        $this->mtgDataProvider = $mtgDataProvider;
        $this->collectionStatsProvider = $collectionStatsProvider;
        $this->languageMapper = $languageMapper;
        $this->uuidEncoder = $uuidEncoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sets = $this->mtgDataProvider->getSets();
        $symbols = $this->mtgDataProvider->getSymbology();

        $user = null;

        if (count($options['users']) === 1) {
            $user = $options['users'][0];
        }

        $cardSupertypes = $this->collectionStatsProvider->getCardSupertypes($user);
        $cardTypes = $this->collectionStatsProvider->getCardTypes($user);
        $cardSubtypes = $this->collectionStatsProvider->getCardSubtypes($user);
        $colors = $this->collectionStatsProvider->getCardColors($user);
        $rarities = $this->collectionStatsProvider->getCardRarities($user);
        $setCodes = $this->collectionStatsProvider->getCardSetCodes($user, $sets);
        $languages = $this->collectionStatsProvider->getCardLanguages($user);

        if (!$user) {
            $users = [];
            /** @var User $user */
            foreach ($options['users'] as $user) {
                $users[$user->getUserIdentifier()] = $this->uuidEncoder->encode($user->getUuid());
            }

            $builder
                ->add(
                    'users',
                    ChoiceType::class,
                    [
                        'label'       => 'form.cards.collection.search.users',
                        'choices'     => $users,
                        'placeholder' => 'Benutzername',
                        'expanded'    => false,
                        'multiple'    => true,
                        'attr'        => [
                            'class' => 'js-select js-select-users',
                        ],
                    ]
                )
            ;
        }

        $builder
            ->add(
                'term',
                SearchType::class,
                [
                    'required' => false,
                    'label'    => 'form.cards.collection.search.term',
                ]
            )
            ->add(
                'order',
                ChoiceType::class,
                [
                    'required' => false,
                    'label'    => 'form.cards.collection.search.order',
                    'choices'  => [
                        'form.cards.collection.search.order_choices.standard'      => '',
                        'form.cards.collection.search.order_choices.id'            => 'id',
                        'form.cards.collection.search.order_choices.quantity'      => 'quantity',
                        'form.cards.collection.search.order_choices.foil_quantity' => 'foilQuantity',
                        'form.cards.collection.search.order_choices.rarity'        => 'rarity',
                        'form.cards.collection.search.order_choices.set_code'      => 'setCode',
                        'form.cards.collection.search.order_choices.language'      => 'language',
                        'form.cards.collection.search.order_choices.mana_cost'     => 'manaCost',
                        'form.cards.collection.search.order_choices.type'          => 'type',
                        'form.cards.collection.search.order_choices.price'         => 'price',
                        'form.cards.collection.search.order_choices.foil_price'    => 'foilPrice',
                    ],
                ]
            )
            ->add(
                'dir',
                ChoiceType::class,
                [
                    'required' => false,
                    'label'    => 'form.cards.collection.search.direction',
                    'choices'  => [
                        'form.cards.collection.search.direction_choices.auto' => '',
                        'form.cards.collection.search.direction_choices.asc'  => 'asc',
                        'form.cards.collection.search.direction_choices.desc' => 'desc',
                    ],
                ]
            )
            ->add(
                'supertypes',
                ChoiceType::class,
                [
                    'required'    => false,
                    'label'       => 'form.supertypes',
                    'choices'     => array_combine($cardSupertypes, $cardSupertypes),
                    'placeholder' => 'Alle',
                    'expanded'    => false,
                    'multiple'    => true,
                    'choice_translation_domain' => false,
                    'attr'        => [
                        'class' => 'js-select js-select-supertypes',
                    ],
                ]
            )
            ->add(
                'types',
                ChoiceType::class,
                [
                    'required'    => false,
                    'label'       => 'form.types',
                    'choices'     => array_combine(array_map(static function (string $type) {
                        return 'card.type.' . strtolower($type);
                    }, $cardTypes), $cardTypes),
                    'placeholder' => 'Alle',
                    'expanded'    => false,
                    'multiple'    => true,
                    'attr'        => [
                        'class' => 'js-select js-select-types',
                    ],
                ]
            )
            ->add(
                'subtypes',
                ChoiceType::class,
                [
                    'required'    => false,
                    'label'       => 'form.subtypes',
                    'choices'     => array_combine($cardSubtypes, $cardSubtypes),
                    'placeholder' => 'Alle',
                    'expanded'    => false,
                    'multiple'    => true,
                    'choice_translation_domain' => false,
                    'attr'        => [
                        'class' => 'js-select js-select-subtypes',
                    ],
                ]
            )
            ->add(
                'colors',
                ChoiceType::class,
                [
                    'required'    => false,
                    'label'       => 'form.colors',
                    'choices'     => array_combine(array_map(static function (string $color) {
                        return 'card.color.' . strtolower($color);
                    }, $colors), $colors),
                    'placeholder' => 'Alle',
                    'expanded'    => false,
                    'multiple'    => true,
                    'attr'        => [
                        'class' => 'js-select js-select-colors',
                    ],
                    'choice_attr' => static function($choice, $key, $value) use ($symbols) {
                        return ['data-icon' => $symbols['{' . $value . '}']->getSvgUri()];
                    },
                ]
            )
            ->add(
                'colorMatch',
                ChoiceType::class,
                [
                    'label'    => 'form.color_matches',
                    'choices'  => [
                        // "Include" means cards that are all the colors you select, with or without any others.
                        // "At most" means cards that have some or all of the colors you select, plus colorless.
                        'form.color_match.exact'   => 'exact',
                        'form.color_match.include' => 'include',
                        'form.color_match.at_most' => 'atMost',
                    ],
                    'expanded' => false,
                    'multiple' => false,
                ]
            )
            ->add(
                'rarities',
                ChoiceType::class,
                [
                    'required'    => false,
                    'label'       => 'form.rarities',
                    'choices'     => array_combine(array_map(static function (string $rarity) {
                        return 'card.rarity.' . strtolower($rarity);
                    }, $rarities), $rarities),
                    'placeholder' => 'Alle',
                    'expanded'    => false,
                    'multiple'    => true,
                    'attr'        => [
                        'class' => 'js-select js-select-rarities',
                    ],
                ]
            )
            ->add(
                'setCodes',
                ChoiceType::class,
                [
                    'required'                  => false,
                    'label'                     => 'form.set_codes',
                    'choices'                   => $setCodes,
                    'placeholder'               => 'Alle',
                    'expanded'                  => false,
                    'multiple'                  => true,
                    'choice_translation_domain' => false,
                    'attr'                      => [
                        'class'     => 'js-select js-select-set-codes',
                    ],
                    'choice_attr'               => static function($choice, $key, $value) use ($sets) {
                        return ['data-icon' => $sets[$value]->getSvgUri()];
                    },
                ]
            )
            ->add(
                'languages',
                ChoiceType::class,
                [
                    'required'    => false,
                    'label'       => 'form.languages',
                    'choices'     => $languages,
                    'placeholder' => 'Alle',
                    'expanded'    => false,
                    'multiple'    => true,
                    'attr'        => [
                        'class' => 'js-select js-select-languages',
                    ],
                    'choice_attr' => function($choice, $key, $value) {
                        return ['data-lang' => $this->languageMapper->languageToCountry($value)];
                    },
                ]
            )
            ->add(
                'isNormal',
                ChoiceType::class,
                [
                    'required' => false,
                    'label'    => 'form.cards.collection.search.is_normal',
                    'choices'  => [
                        'form.cards.collection.search.is_normal_choices.yes' => 'y',
                        'form.cards.collection.search.is_normal_choices.no'  => 'n',
                    ]
                ]
            )
            ->add(
                'isFoil',
                ChoiceType::class,
                [
                    'required' => false,
                    'label'    => 'form.cards.collection.search.is_foil',
                    'choices'  => [
                        'form.cards.collection.search.is_foil_choices.yes' => 'y',
                        'form.cards.collection.search.is_foil_choices.no'  => 'n',
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'csrf_protection'    => false,
                'allow_extra_fields' => true,
                'users'              => [],
            ])
            ->setRequired([
                'users',
            ])
        ;
    }
}
