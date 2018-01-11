<?php

namespace CRT\ToolBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CRT\ToolBundle\Validator\Constraints\UserNotExist;
use CRT\ToolBundle\Entity\AcademyRepository;

class RequestType extends AbstractType
{
    private $preferred;

    public function __construct($preferred)
    {
        $this->preferred = $preferred;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $academies = $this->preferred;
        $builder
            ->add('firstName', 'text',
                array(
                    'label' => 'Prénom ',
                    'attr' => array(
                        'placeholder' => 'Votre prénom',
                    )
                )
            )
            ->add('lastName','text',
                array(
                    'label' => 'Nom ',
                    'attr' => array(
                        'placeholder' => 'Votre nom',
                    )
                )
            )
            ->add('email', 'email',
                array(
                    //'constraints' => array(
                    //    new UserNotExist(),
                    //),
                    'label' => 'Adresse e-mail ',
                    'attr' => array(
                        'placeholder' => 'Votre adrese e-mail',
                    )
                )
            )
            ->add('academy', 'entity',
                array(
                    'label' => 'Académie de rattachement ',
                    'class' => 'CRTToolBundle:Academy',
                    'empty_value' => 'Merci de sélectionner votre académie',
                    'property' => 'label',
                    'preferred_choices' => $academies,
                )
            )
            ->add('corporate', 'entity',
                array(
                    'class' => 'CRTToolBundle:Corporate',
                    'property' => 'label',
                    'label' => 'Entité du CNS ',
                    'empty_value' => 'Merci de sélectionner votre entité',
                )
            )
            ->add('title', 'text',
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
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CRT\ToolBundle\Entity\Request'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'crt_toolbundle_request';
    }
}
