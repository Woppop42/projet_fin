<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Plateforme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UpdateProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            #->add('email')
            #->add('password')
            ->add('pseudo')
            #->add('roles')
            ->add('photo_profil', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpg',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, PNG ou JPG valide.'])
                ]
            ])  
            // ->add('photos', ChoiceType::class, [
            //     'label' => 'Photo de profil',
            //     'required' => false,
            //     'choices' => $options['choices'],
            //     'multiple' => false,
            //     'choice_label' => false,
            //     'constraints' => [
            //         new File([
            //             'maxSize' => '1024k',
            //             'mimeTypes' => [
            //                 'application/pdf',
            //                 'application/x-pdf',
            //                 'image/jpg',
            //                 'image/jpeg',
            //                 'image/png'
            //             ],
            //             'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, PNG ou JPG valide.'])
            //     ]
                
            //         ])
            ->add('plateformes', EntityType::class, [
                'class' => Plateforme::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                
            ]
            )
            #->add('titres')
            #->add('date_inscription')
            #->add('jeux')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'choices' => []
        ]);
    }
}
