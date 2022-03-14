<?php

namespace SimpleIT\ClaireExerciseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SimpleIT\ClaireExerciseBundle\Repository\DirectoryRepository;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class AskerUserDirectoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $options['userId'];
        $builder
            ->add('isManager', CheckboxType::class,
                array(
                    'label' => " ", # do not display "is Manager"
                    'required' => false,
                )
            )
            ->add('isOwner',CheckboxType::class,
                array(
                    'label' => " ",
                    'disabled' => 'disabled',
                )
            )
            #->add('startDate', HiddenType::class,
            #    array(
            #        'data' => new \DateTime()
            #    )
            #)
            ->add('directory', EntityType::class,
                array(
                    'choice_label' => 'name',
                    'class' => 'SimpleITClaireExerciseBundle:Directory',
                    'query_builder' => function(DirectoryRepository $dr) use($userId){
                        return $dr->findObjects();
                        #return $dr->findObjectNotMine($userId);
                        return $dr->findObjectParents();
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
        $resolver->setDefaults(array(
            'data_class' => 'SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory'
        ));
        $resolver->setRequired(array(
            'userId'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'simpleit_claireexercisebundle_askeruserdirectory';
    }
}
