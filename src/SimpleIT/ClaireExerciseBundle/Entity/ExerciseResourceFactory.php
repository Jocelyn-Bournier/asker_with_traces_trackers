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

use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\ExerciseResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ResourceResource;

/**
 * Class to manage the creation of ExerciseResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ExerciseResourceFactory extends SharedEntityFactory
{
    /**
     * Create a new ExerciseResource object
     *
     * @param string $content Content
     *
     * @return ExerciseResource
     */
    public static function create($content = '')
    {
        $exerciseResource = new ExerciseResource();
        parent::initialize($exerciseResource, $content);

        return $exerciseResource;
    }

    /**
     * Create an exerciseResource entity from a resourceResource and the author
     *
     * @param ResourceResource $resourceResource
     *
     * @return ExerciseResource
     */
    public static function createFromResource(
        ResourceResource $resourceResource
    )
    {
        $exerciseResource = new ExerciseResource();
        parent::fillFromResource($exerciseResource, $resourceResource, 'resource_storage');

        return $exerciseResource;
    }
}
