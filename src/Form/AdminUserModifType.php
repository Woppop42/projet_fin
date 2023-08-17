<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminUserModifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            #->add('email')
            #->add('password')
            ->add('pseudo')
            ->add('roles', ChoiceType::class, [
                "choices" => [
                    "Administrateur" => 'Administrateur',
                    "Utilisateur" => 'Utilisateur',

                ]
            ])
            #->add('photo_profil')
            #->add('titres')
            #->add('date_inscription')
            #->add('jeux')
        ;

        $builder->get('roles')
                ->addModelTransformer(new CallbackTransformer(
                    function ($rolesArray) {
                         // transform the array to a string
                         return count($rolesArray)? $rolesArray[0]: null;
                    },
                    function ($rolesString) {
                         // transform the string back to an array
                         return [$rolesString];
                    }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
