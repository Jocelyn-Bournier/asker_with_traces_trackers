<?php

namespace SimpleIT\ClaireExerciseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SimpleIT\ClaireExerciseBundle\Repository\DirectoryRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


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
            ->add('isManager')
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
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
    public function getName()
    {
        return 'simpleit_claireexercisebundle_askeruserdirectory';
    }
}
