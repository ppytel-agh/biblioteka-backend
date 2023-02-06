<?php

namespace App\Form;

use App\Database\Generator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CzytelnikType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $generator = new Generator();
        $pregeneratedInput = $_ENV['GENERATE_NUMBERS'] ? $generator->getNumerKarty() : null;
        $builder
            ->add('numer_karty', TextType::class, ['data'=>$pregeneratedInput])
            ->add('Zarejestruj', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
