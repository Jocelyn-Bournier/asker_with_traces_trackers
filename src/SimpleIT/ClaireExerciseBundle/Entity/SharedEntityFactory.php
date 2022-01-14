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

namespace SimpleIT\ClaireExerciseBundle\Entity;

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\SharedEntity;
use SimpleIT\ClaireExerciseBundle\Model\Resources\SharedResource;
use SimpleIT\ClaireExerciseBundle\Serializer\Handler\AbstractClassForExerciseHandler;

/**
 * Class to manage the creation of ExerciseModel
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class SharedEntityFactory
{
    /**
     * Initialize an entity
     *
     * @param SharedEntity $entity
     * @param string       $content
     */
    protected static function initialize(&$entity, $content = '')
    {
        $entity = new ExerciseModel();
        $entity->setContent($content);
        $entity->setComplete(false);
        $entity->setPublic(false);
        $entity->setArchived(false);
    }

    /**
     * Fill an entity from a resource
     *
     * @param SharedEntity   $entity
     * @param SharedResource $resource
     * @param string         $serializationGroup
     */
    public static function fillFromResource(
        SharedEntity &$entity,
        &$resource,
        $serializationGroup = 'Default'
    )
    {
        $entity->setId($resource->getId());
        $entity->setType($resource->getType());
        $entity->setTitle($resource->getTitle());
        $entity->setPublic($resource->getPublic());
        $entity->setArchived($resource->getArchived());
        $entity->setDraft($resource->getDraft());
        $entity->setComplete($resource->getComplete());

        // content
        $serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerSubscribingHandler(new AbstractClassForExerciseHandler());
                }
            )
            ->build();
        $context = SerializationContext::create();
        $context->setGroups(array($serializationGroup, 'Default'));
        $content = $serializer->serialize($resource->getContent(), 'json', $context);
        $entity->setContent($content);
    }
}
