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

use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseDocumentObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\DocumentResource;

/**
 * Factory to create ExerciseDocument
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ExerciseDocumentFactory
{
    /**
     * Create ExerciseDocument from ExerciseResource
     *
     * @param DocumentResource $res The input resource
     *
     * @return ExerciseDocumentObject
     */
    public static function createFromCommonResource(DocumentResource $res)
    {
        $document = new ExerciseDocumentObject();
        $document->setSource($res->getSource());

        return $document;
    }
}
