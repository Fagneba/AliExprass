<?php

namespace App\Form;
use App\Form\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];//Recuperons la clé user grace à l'option qui nous permet d'envoyer des donnnées

        $builder
            ->add('address', EntityType::class,[ // Comme notre formulaire n'est pas lié à une entité , on précise dans quelle entité nous allons travailler
                'class' => Address::class,  // On precise ici l'entité Address.php dans la classe address
                'required' => true, // Pour dire que ce champs est requit , est obligatoire 
                'choices' => $user->getAddresses(), // Recupère nous toutes les adresses de l'utilisateur d'où array $option
                'multiple' => false, // L'utilisateur doit choisir une et une seul adresse de livraison ,meme s'il en a plusieur.
                'expanded' => true   // pour afficher un chekboxe pour la beauté pour le style

            ] )

            ->add('carrier', EntityType::class,[ // Comme notre formulaire n'est pas lié à une entité , on précise dans quelle entité nous allons travailler
                'class' => Carrier::class,  // On precise ici l'entité Address.php dans la classe address
                'required' => true, // Pour dire que ce champs est requit , est obligatoire 
                'multiple' => false, // L'utilisateur doit choisir une et une seul adresse de livraison ,meme s'il en a plusieur.
                'expanded' => true   // pour afficher un chekboxe pour la beauté pour le style

            ] )
        
            ->add('informations', TextareaType::class, [
                'required'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'user' => array(), // On donne une valeur par defaut à notre user : c'est un tableau vide
        ]);
    }
}
