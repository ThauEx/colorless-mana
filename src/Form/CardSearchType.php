<?php

namespace App\Form;

use App\DataProvider\MtgDataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardSearchType extends AbstractType
{

    public function __construct(private readonly MtgDataProvider $mtgDataProvider)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sets = $this->mtgDataProvider->getSets();
        $setNames = [];
        foreach ($sets as $set) {
            $setCode = $set->getCode();
            $setNames[$set->getName() . ' (' . $setCode . ')'] = $setCode;
        }

        ksort($setNames);

        $builder
            ->add(
                'scryfall_id',
                TextType::class,
                [
                    'required' => false,
                    'attr'     => [
                        'placeholder' => 'form.card_search.scryfall_id',
                    ],
                ]
            )
            ->add(
                'submit_scryfall',
                SubmitType::class,
                [
                    'label' => 'form.card_search.scryfall_submit',
                ]
            )
            ->add(
                'setCode',
                ChoiceType::class,
                [
                    'required'                  => false,
                    'placeholder'               => 'form.card_search.set_code',
                    'choices'                   => $setNames,
                    'choice_translation_domain' => false,
                    'attr'                      => [
                        'class'          => 'js-select js-select-set-codes',
                        'data-max-items' => 1,
                    ],
                    'choice_attr'               => static function($choice, $key, $value) use ($sets) {
                        return ['data-icon' => $sets[$value]->getSvgUri()];
                    },
                ]
            )
            ->add(
                'number',
                TextType::class,
                [
                    'required' => false,
                    'attr'     => [
                        'placeholder' => 'form.card_search.number',
                    ],
                ]
            )
            ->add(
                'submit_set_number',
                SubmitType::class,
                [
                    'label' => 'form.card_search.set_number_submit',
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'required' => false,
                    'attr'     => [
                        'placeholder' => 'form.card_search.name',
                    ],
                ]
            )
            ->add(
                'submit_name',
                SubmitType::class,
                [
                    'label' => 'form.card_search.name_submit',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
