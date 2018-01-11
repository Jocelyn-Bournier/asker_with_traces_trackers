<?php

namespace CRT\ToolBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CRT\ToolBundle\Validator\Constraints\UserNotExist;

class AccountType extends AbstractType
{
    public function BuildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text',
                array(
                    'label' => 'Prénom ',
                    'attr' => array(
                        'placeholder' => 'Votre prénom',
                    )
                )
            )
            ->add('lastname', 'text',
                array(
                    'label' => 'Nom ',
                    'attr' => array(
                        'placeholder' => 'Votre prénom',
                    )
                )
            )
            ->add('email', 'email',
                array(
                    'constraints' => array(
                        new UserNotExist(),
                    ),
                    'label' => 'Adresse e-mail ',
                    'attr' => array(
                        'placeholder' => 'Votre adrese e-mail',
                    )
                )
            )
            ->add('academy', 'choice',
                array(
                    'label' => 'Académie de rattachement ',
                    'preferred_choices' => array(
                        "Adminstration-Centrale",
                        "ac-aix-marseille",
                        "ac-amiens",
                        "siec",
                        "ac-besancon",
                        "ac-bordeaux",
                        "ac-caen",
                        "ac-clermont",
                        "ac-corse",
                        "ac-creteil",
                        "ac-dijon",
                        "ac-grenoble",
                        "ac-guadeloupe",
                        "ac-guyane",
                        "ac-lille",
                        "ac-limoges",
                        "ac-lyon",
                        "ac-martinique",
                        "ac-mayote",
                        "ac-montpellier",
                        "ac-nancy-metz",
                        "ac-nantes",
                        "ac-nice",
                        "ac-noumea",
                        "ac-orleans-tours",
                        "ac-paris",
                        "ac-poitiers",
                        "ac-polynesie",
                        "ac-reims",
                        "ac-rennes",
                        "ac-reunion",
                        "ac-rouen",
                        "ac-strasbourg",
                        "ac-toulouse",
                        "ac-versailles",
                        "ac-wf",
                    ),
                    'empty_value' => 'Merci de sélectionner votre académie',
                    'choices' => array(
                        "education" => "Administration Centrale",
                        "ac-aix-marseille" => "Aix-Marseille",
                        "ac-amiens" => "Amiens",
                        "siec" => "Arcueil",
                        "ac-besancon" => "Besançon",
                        "ac-bordeaux" => "Bordeaux",
                        "ac-caen" => "Caen",
                        "ac-clermont" => "Clermont-Ferrand",
                        "ac-corse" => "Corse",
                        "ac-creteil" => "Créteil",
                        "ac-dijon" => "Dijon",
                        "ac-grenoble" => "Grenoble",
                        "ac-guadeloupe" => "Guadeloupe",
                        "ac-guyane" => "Guyane",
                        "ac-lille" => "Lille",
                        "ac-limoges" => "Limoges",
                        "ac-lyon" => "Lyon",
                        "ac-martinique" => "Martinique",
                        "ac-mayote" => "Mayotte",
                        "ac-montpellier" => "Montpellier",
                        "ac-nancy-metz" => "Nancy-Metz",
                        "ac-nantes" => "Nantes",
                        "ac-nice" => "Nice",
                        "ac-noumea" => "Nouvelle-Calédonie",
                        "ac-orleans-tours" => "Orleans-Tours",
                        "ac-paris" => "Paris",
                        "ac-poitiers" => "Poitiers",
                        "ac-polynesie" => "Polynesie",
                        "ac-reims" => "Reims",
                        "ac-rennes" => "Rennes",
                        "ac-reunion" => "Réunion",
                        "ac-rouen" => "Rouen",
                        "ac-strasbourg" => "Strasbourg",
                        "ac-toulouse" => "Toulouse",
                        "ac-versailles" => "Versailles",
                        "ac-wf" => "Wallis et Futuna",
                        "Prestataire" => "Prestataire hébergé en dehors d'une entité"
                    )
                )
            )
            ->add('entity', 'choice',
                array(
                    'label' => 'Entité du CNS ',
                    'empty_value' => 'Merci de sélectionner votre entité',
                    'choices' => array(
                        'CES_CHOREGIE' => 'CES CHOREGIE',
                        'CES_DECISIONNEL' => 'CES DECISIONNEL',
                        'CES_FOAD' => 'CES FOAD',
                        'CES_GOSPEL' => 'CES GOSPEL',
                        'CES_SIRHEN' => 'CES SIRHEN',
                        'CNS_DIR' => 'CNS DIR',
                        'CNS_UC' => 'CNS UC',
                        'CRT_ECD' => 'CRT ECD',
                        'CRT_FIM' => 'CRT FIM',
                        'CRT_HEB' => 'CRT HEB',
                        'CRT_ITIL' => 'CRT ITIL',
                        'CRT_RSO' => 'CRT RSO',
                        'CRT_SUP' => 'CRT SUP',
                        'ITP' => 'ITP',
                        'SIRHEN_INTEGRATION' => 'SIRHEN INTEGRATION',
                    )
                )
            )
            ->add('function', 'text',
                array(
                    'label' => 'Votre fonction ',
                    'required' => false,
                )
            )
            ->add('deskPhone', 'text',
                array(
                    'label' => 'Numéro de téléphone de bureau ',
                    'required' => false,
                )
            )
            ->add('mobiPhone', 'text',
                array(
                    'label' => 'Numéro de téléphone portable ',
                    'required' => false,
                )
            )
            ->add('save', 'submit',
                array(
                    'label'=> 'Soumettre ma demande',
                    'attr' => array(
                        'class' => 'btn btn-lg btn-success btn-block',
                        'style' => 'margin-top:15px;'
                    )
                )
            )
        ;
    }

    public function getName()
    {
        return 'crt_tool_account';
    }
}
