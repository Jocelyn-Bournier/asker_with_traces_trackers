<?php

namespace SimpleIT\ClaireExerciseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AskerUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class,
                array(
                    'label' => 'Identifiant',
                )
            )
            ->add('firstName', TextType::class,
                array(
                    'label' => 'Prénom',
                )
            )
            ->add('lastName', TextType::class,
                array(
                    'label' => 'Nom de famille',
                )
            )
            ->add('isEnable', CheckboxType::class,
                array(
                    'label' => 'Compte actif',
                    'required' => false,
                )
            )
            ->add('isLdap', CheckboxType::class,
                array(
                    'label' => 'Compte LDAP',
                    'required' => false,
                )
            )
            ->add('ldapDn', TextType::class,
                array(
                    'label' => 'DN si compte LDAP',
                    'required' => false,
                )
            )
            ->add('roles', EntityType::class,
                array(
                    'class' => 'SimpleITClaireExerciseBundle:Role',
                    'choice_label' => 'public',
                    'multiple' => true,
                    'expanded' => true,
                )
            )
            ->add('directories', EntityType::class,
                array(
                    'class' => 'SimpleITClaireExerciseBundle:Directory',
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'by_reference' => false,
                )
            )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleIT\ClaireExerciseBundle\Entity\AskerUser'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'simpleit_claireexercisebundle_askeruser';
    }
}
