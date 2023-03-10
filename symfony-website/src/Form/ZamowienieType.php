<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ZamowienieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('data_nabycia', DateType::class)
            ->add('liczba_sztuk',
                IntegerType::class,
                [
                    'constraints' => [
                        new Range(
                            [
                                'min'=>1,
                                'max'=>10,
                                'notInRangeMessage' => 'Liczba zamówionych sztuk musi zawierać się między {{ min }}, a {{ max }}'
                            ]
                        )
                    ]
                ]
            )
            ->add('zarejestruj', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
