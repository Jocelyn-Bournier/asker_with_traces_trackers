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

use SimpleIT\ClaireExerciseBundle\Repository\DirectoryRepository;
use SimpleIT\ClaireExerciseBundle\Repository\AskerUserDirectoryRepository;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory;
use Doctrine\Common\Collections\ArrayCollection;

class AskerUserType extends AbstractType
{

    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $builder->getData()->getId();
        $datas = new ArrayCollection($this->em
            ->getRepository('SimpleITClaireExerciseBundle:AskerUserDirectory')
            ->findMyParents($userId)
        );
        $builder
            ->add('username', TextType::class,
                array(
                    'label' => 'Identifiant',
                    'block_name' => 'hello'
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
            ->add('directories',CollectionType::class,
                array(
                    'entry_type' => AskerUserDirectoryType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'data' => $datas,
                    'entry_options' => array(
                        'label' => false,
                        'userId' => $userId
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

