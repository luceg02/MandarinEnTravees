<?php
// src/Form/ReportType.php

namespace App\Form;

use App\Entity\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raison', ChoiceType::class, [
                'choices' => [
                    'Contenu inapproprié' => 'inapproprie',
                    'Spam' => 'spam',
                    'Information incorrecte' => 'incorrecte',
                    'Harcèlement' => 'harcelement',
                    'Autre' => 'autre'
                ],
                'label' => 'Raison du signalement',
                'attr' => ['class' => 'form-select']
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire (optionnel)',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Précisez votre signalement si nécessaire...'
                ]
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Envoyer le signalement',
                'attr' => ['class' => 'btn btn-danger']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}