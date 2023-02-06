<?php

namespace App\Form;

use App\Database\Generator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PozycjaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $generator = new Generator();
        $pregeneratedInput = $_ENV['GENERATE_NUMBERS'] ? $generator->getRandomDigitString(rand()%2 ? 10 : 13) : null;
        $builder
            ->add('ISBN', TextType::class, [
                'data' => $pregeneratedInput,
            ])
            ->add('tytul', TextType::class, ['label'=>'Tytuł'])
            ->add('data_wydania', DateType::class, [
                'widget' => 'single_text',
                'label'=>'Data wydania',
            ])
            ->add('dodaj_pozycje', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
