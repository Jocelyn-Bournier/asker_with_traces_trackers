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

use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OpenEndedQuestionResource;

/**
 * This class manages the creation of instances of OpenEndedQuestion.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class OpenEndedQuestionFactory
{
    /**
     * Create a model question from an exerciseResource
     *
     * @param OpenEndedQuestionResource $res The resource
     *
     * @return OpenEndedQuestion
     */
    public static function createFromCommonResource(OpenEndedQuestionResource $res)
    {
        $oeq = new OpenEndedQuestion();

        $oeq->setComment($res->getComment());
        $oeq->setQuestion($res->getQuestion());
        $oeq->setSolutions($res->getSolutions());

        return $oeq;
    }
}
