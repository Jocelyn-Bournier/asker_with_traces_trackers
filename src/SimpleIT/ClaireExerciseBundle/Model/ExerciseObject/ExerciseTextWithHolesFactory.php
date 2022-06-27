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

namespace SimpleIT\ClaireExerciseBundle\Model\ExerciseObject;

use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseTextObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextWithHolesResource;

/**
 * Factory to create a text object for an exercise.
 *
 * @author Valentin Lachand-Pascal <valentin.lachand@liris.cnrs.fr>
 */
abstract class ExerciseTextWithHolesFactory
{
    /**
     * Create a text object for exercise from an exercise resource.
     *
     * @param TextWithHolesResource $res The resource
     *
     * @return ExerciseTextWithHoles
     */
    public static function createFromCommonResource(TextWithHolesResource $res) : ExerciseTextWithHoles
    {
        $textObj = new ExerciseTextWithHoles();

        $textObj->setText($res->getText());
        $textObj->setBold($res->getBold());
        $textObj->setItalize($res->getItalize());
        $textObj->setAnnotations($res->getAnnotations());
        $textObj->setAnnotationsList($res->getAnnotationsList());
        $textObj->setErrorsList($res->getErrorsList());
        $textObj->setUnderline($res->getUnderline());

        return $textObj;
    }


}
