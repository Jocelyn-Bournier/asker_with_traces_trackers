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

use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResource;

/**
 * Class to manage the creation of ExerciseModel
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ExerciseModelFactory extends SharedEntityFactory
{
    /**
     * Create a new ExerciseModel object
     *
     * @param string $content Content
     *
     * @return ExerciseModel
     */
    public static function createExerciseModel($content = '')
    {
        $exerciseModel = new ExerciseModel();
        parent::initialize($exerciseModel, $content);

        return $exerciseModel;
    }

    /**
     * Create an exerciseModel entity from a resource and the author
     *
     * @param ExerciseModelResource $modelResource
     *
     * @return ExerciseModel
     */
    public static function createFromResource(
        ExerciseModelResource $modelResource
    )
    {
        $model = new ExerciseModel();
        parent::fillFromResource($model, $modelResource, 'exercise_model_storage');

        return $model;
    }
}
