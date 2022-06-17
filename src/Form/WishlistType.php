<?php

namespace App\Form;

use App\DataProvider\MtgDataProvider;
use App\Entity\Card;
use App\Entity\Wishlist;
use App\Helper\LanguageMapper;
use App\Repository\CardRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WishlistType extends AbstractType
{
    private MtgDataProvider $mtgDataProvider;
    private LanguageMapper $languageMapper;

    public function __construct(MtgDataProvider $mtgDataProvider,LanguageMapper $languageMapper)
    {
        $this->mtgDataProvider = $mtgDataProvider;
        $this->languageMapper = $languageMapper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sets = $this->mtgDataProvider->getSets();

        $builder
            ->add(
                'languages',
                ChoiceType::class,
                [
                    'required'    => false,
                    'label'       => 'form.wishlist_add.languages',
                    'choices'     => $this->languageMapper->getMapping(),
                    'placeholder' => 'form.wishlist_add.languages_placeholder',
                    'expanded'    => false,
                    'multiple'    => true,
                    'choice_translation_domain' => false,
                    'attr'        => [
                        'class' => 'js-select js-select-languages',
                    ],
                    'choice_attr' => function($choice, $value) {
                        $language = $this->languageMapper->languageToCode($value);
                        return ['data-lang' => $this->languageMapper->languageToCountry($language)];
                    },
                ]
            )
            ->add(
                'nonFoilQuantity',
                IntegerType::class,
                [
                    'label' => 'form.wishlist_add.non_foil_quantity',
                    'attr'  => [
                        'min' => 0,
                    ],
                ]
            )
            ->add(
                'foilQuantity',
                IntegerType::class,
                [
                    'label' => 'form.wishlist_add.foil_quantity',
                    'attr'  => [
                        'min' => 0,
                    ],
                ]
            )
            ->add(
                'cards',
                EntityType::class,
                [
                    'required'      => false,
                    'label'         => 'form.wishlist_add.cards',
                    'class'         => Card::class,
                    'query_builder' => function (CardRepository $repo) use ($options) {
                        return $repo->createQueryBuilder('c')
                            ->where('c.scryfallOracleId = :scryfallOracleId')
                            ->setParameter('scryfallOracleId', $options['scryfall_oracle_id'])
                        ;
                    },
                    'placeholder'   => 'form.wishlist_add.cards_placeholder',
                    'choice_label'  => function (Card $card) use ($sets) {
                        return $sets[$card->getSetCode()]->getName() . ' (' . $card->getSetCode() . ') ' . $card->getNumber();
                    },
                    'expanded'      => false,
                    'multiple'      => true,
                    'attr'          => [
                        'class' => 'js-select js-select-sets',
                    ],
                    'choice_attr'   => static function($choice) use ($sets) {
                        return ['data-icon' => $sets[$choice->getSetCode()]->getSvgUri()];
                    },
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Wishlist::class,
            'scryfall_oracle_id' => '',
        ]);
    }
}
