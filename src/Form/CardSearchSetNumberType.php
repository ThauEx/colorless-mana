<?php

namespace App\Form;

use App\DataProvider\MtgDataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CardSearchSetNumberType extends AbstractType
{
    public function __construct(private readonly MtgDataProvider $mtgDataProvider)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sets = $this->mtgDataProvider->getSets();
        $setCodes = [];
        foreach ($sets as $set) {
            $setCode = $set->getCode();
            $setCodes[$set->getName() . ' (' . $setCode . ')'] = $setCode;
        }

        ksort($setCodes);

        $builder
            ->add(
                'setCode',
                ChoiceType::class,
                [
                    'label'                     => false,
                    // TomSelect hides the original select, so the browser cannot
                    // focus it to report a native validation error
                    'required'                  => false,
                    'placeholder'               => 'form.card_search.set_code',
                    'choices'                   => $setCodes,
                    'choice_translation_domain' => false,
                    'constraints'               => [new NotBlank()],
                    'attr'                      => [
                        'class'          => 'js-select js-select-set-codes',
                        'data-max-items' => 1,
                    ],
                    'choice_attr'               => static fn($choice, $key, $value) => ['data-icon' => $sets[$value]->getSvgUri()],
                ]
            )
            ->add(
                'number',
                TextType::class,
                [
                    'label'    => false,
                    'required' => false,
                    'attr'     => [
                        'placeholder' => 'form.card_search.number',
                    ],
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.card_search.set_number_submit',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method'          => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'set';
    }
}
