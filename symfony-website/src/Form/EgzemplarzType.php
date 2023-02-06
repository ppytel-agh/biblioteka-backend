<?php

namespace App\Form;

use App\Database\Generator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EgzemplarzType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $generator = new Generator();
        $pregeneratedInput = $_ENV['GENERATE_NUMBERS'] ? $generator->getNumerEgzemplarza() : null;
        $builder
            ->add('numer', TextType::class, ['data'=>$pregeneratedInput])
            ->add('data_nabycia', DateType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime('now')
            ])
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
