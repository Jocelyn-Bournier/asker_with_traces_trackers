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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Knowledge;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\ExerciseResource;

/**
 * Class ResourceResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ResourceResourceFactory extends SharedResourceFactory
{

    /**
     * Create an ResourceResource collection
     *
     * @param array $resources
     *
     * @return array
     */
    public static function createCollection(array $resources)
    {
        $resourceResources = array();
        foreach ($resources as $resource) {
            $resourceResources[] = self::create($resource);
        }

        return $resourceResources;
    }

    /**
     * Create a ResourceResource
     *
     * @param ExerciseResource $resource
     *
     * @return ResourceResource
     */
    public static function create(ExerciseResource $resource)
    {
        $resourceResource = new ResourceResource();
        parent::fill(
            $resourceResource,
            $resource
        );

        // required resources
        $requirements = array();
        foreach ($resource->getRequiredExerciseResources() as $req) {
            /** @var ExerciseResource $req */
            $requirements[] = $req->getId();
        }
        $resourceResource->setRequiredExerciseResources($requirements);

        // required resources
        $requirements = array();
        foreach ($resource->getRequiredKnowledges() as $req) {
            /** @var Knowledge $req */
            $requirements[] = $req->getId();
        }
        $resourceResource->setRequiredKnowledges($requirements);

        // removable
        if (count($resource->getRequiredByModels()) > 0
            || count($resource->getRequiredByResources()) > 0
        ) {
            $resourceResource->setRemovable(false);
        } else {
            $resourceResource->setRemovable(true);
        }

        return $resourceResource;
    }
}
