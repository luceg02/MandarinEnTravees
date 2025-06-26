<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Demande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class DemandeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la demande',
                'help' => 'Décrivez brièvement l\'information à vérifier (minimum 10 caractères)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Le gouvernement va-t-il augmenter les taxes sur l\'essence ?',
                    'maxlength' => 255
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre est obligatoire'
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le titre doit faire au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée',
                'help' => 'Expliquez le contexte et pourquoi cette information doit être vérifiée (minimum 50 caractères)',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Contexte: Où avez-vous vu cette information ? Pourquoi avez-vous des doutes ? Quels éléments vous semblent suspects ?'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description est obligatoire'
                    ]),
                    new Length([
                        'min' => 50,
                        'minMessage' => 'La description doit faire au moins {{ limit }} caractères',
                        'max' => 2000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            
            ->add('liensSources', TextareaType::class, [
                'label' => 'Liens sources (optionnel)',
                'help' => 'URLs des articles, posts de réseaux sociaux ou autres sources (un lien par ligne)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => "https://example.com/article1\nhttps://twitter.com/user/status/123\nhttps://facebook.com/post/456"
                ]
            ])
            
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez une catégorie',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La catégorie est obligatoire.',
                    ]),
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image ou capture d\'écran (optionnel)',
                'help' => 'Formats acceptés: JPG, PNG, GIF (max 5 Mo)',
                'required' => false,
                'mapped' => false, // Ce champ ne correspond pas directement à une propriété de l'entité
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, GIF, WebP)',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). Taille maximale autorisée : {{ limit }} {{ suffix }}'
                    ])
                ]
            ])
            
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre ma demande',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg w-100 mt-3'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}