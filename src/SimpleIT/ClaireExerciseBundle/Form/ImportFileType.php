<?php

namespace SimpleIT\ClaireExerciseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class ImportFileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attachment', FileType::class,
                array(
                    'label' => 'Liste des utilisateurs',
                    'mapped' => false,
                    'required' => true
                )
            )
            ->add('roles', EntityType::class,
                array(
                    'class' => 'SimpleITClaireExerciseBundle:Role',
                    'choice_label' => 'public',
                    'label' => 'Attriber des rôles supplémentaires',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                  ->where('r.name not like :role')
                              ->setParameter('role','%ROLE_USER%');

                    }
                )
            )
            ->add('directory', EntityType::class,
                array(
                    'class' => 'SimpleITClaireExerciseBundle:Directory',
                    'multiple' => false,
                    'expanded' => false,
                    'label' => 'Attribuer un dossier',
                    'placeholder' => "Aucun dossier",
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                        ->where('d.parent is NULL');
                    }
                )
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        #$resolver->setDefaults(array(
        #    'data_class' => 'SimpleIT\ClaireExerciseBundle\Entity\AskerUser'
        #));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'simpleit_claireexercisebundle_importfile';
    }
}
