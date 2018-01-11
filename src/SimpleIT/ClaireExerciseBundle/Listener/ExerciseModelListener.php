<?php
/*
 * This file is part of CLAIRE.
 *
 * CLAIRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CLAIRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CLAIRE. If not, see <http://www.gnu.org/licenses/>
 */

namespace SimpleIT\ClaireExerciseBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ExerciseModelListener
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseModelListener implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * Set the container
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * On try, open claire to answer the exercise.
     *
     * @param CustomActionResourceEvent $event
     */
    public function onEdit(CustomActionResourceEvent $event)
    {
        /** @var ExerciseModel $model */
        $model = $event->getResource();

        //Redirection to the controller
        $route = $this->container->get('router')->generate('frontend_index');
        $route .= '#/teacher/model/' . $model->getId();
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * On open, open claire to attempt list for this model
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        /** @var ExerciseModel $model */
        $model = $event->getResource();

        //Redirection to the controller
        $route = $this->container->get('router')->generate('frontend_index');
        $route .= '#/learner/model/' . $model->getId();
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * On create form, go to the claire exercise model browser.
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        //Redirection to the controller
        $route = $this->container->get('router')->generate('frontend_index');
        $route .= '#/teacher/model';
        $event->setResponseContent(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * On delete. Archive exercise model
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var ExerciseModel $exerciseModel */
        $exerciseModel = $this->container->get('simple_it.exercise.exercise_model')->get(
            $event->getResource()->getId()
        );

        $resourceNode = $exerciseModel->getResourceNode();
        $exerciseModel->deleteResourceNode();
        $exerciseModel->setArchived(true);
        $em->remove($resourceNode);

        $em->flush();
        exit();
    }

    /**
     * Copy an exercise model
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $original = $event->getResource();

        /** @var ExerciseModel $copy */
        $copy = $this->container->get('simple_it.exercise.exercise_model')->duplicate(
            $original->getId(),
            $this->container->get('security.context')->getToken()->getUser()->getId()
        );

        $event->setCopy($copy);
        $event->stopPropagation();
    }
}
