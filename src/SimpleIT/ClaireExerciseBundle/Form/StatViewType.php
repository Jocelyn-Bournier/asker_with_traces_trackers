<?php

namespace SimpleIT\ClaireExerciseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StatViewType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                array(
                    'label' => 'Nom du filtre',
                )
            )
            ->add('startDate',DateType::class,
                array(
                    'label' => 'Date de début',
                    'widget' => 'single_text',
                )
            )
            ->add('endDate',DateType::class,
                array(
                    'label' => 'Date de fin',
                    'widget' => 'single_text',
                )
            )
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleIT\ClaireExerciseBundle\Entity\StatView'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'simpleit_claireexercisebundle_statview';
    }
}
