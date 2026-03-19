<?php

namespace App\Form;

use App\Entity\Abonne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputClass = 'w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-500';

        $builder
            ->add('prenom', TextType::class, [
                'label'    => 'Prénom',
                'required' => false,
                'attr'     => ['placeholder' => 'Jean (optionnel)', 'class' => $inputClass],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr'  => ['placeholder' => 'jean@example.com', 'class' => $inputClass],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Abonne::class]);
    }
}