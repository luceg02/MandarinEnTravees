<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AnswerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Votre contribution',
                'help' => 'Apportez des éléments de vérification, sources, analyses ou commentaires constructifs.',
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Décrivez votre analyse, ajoutez des sources fiables, ou apportez des éléments de contexte...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le contenu de votre contribution.'
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 5000,
                        'minMessage' => 'Votre contribution doit faire au moins {{ limit }} caractères.',
                        'maxMessage' => 'Votre contribution ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('sources', TextareaType::class, [
                'label' => 'Sources et liens (optionnel)',
                'required' => false,
                'help' => 'Ajoutez des liens vers vos sources (un par ligne).',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => "https://exemple.com/article1\nhttps://exemple.com/article2",
                    'class' => 'form-control'
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image ou capture d\'écran (optionnel)',
                'required' => false,
                'mapped' => false,
                'help' => 'Formats acceptés: JPG, PNG, GIF (max 5Mo)',
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Veuillez sélectionner une image valide (JPG, PNG, GIF, WebP).',
                        'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5Mo.'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier ma contribution',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}