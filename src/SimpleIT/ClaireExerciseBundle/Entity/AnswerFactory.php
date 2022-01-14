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

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Answer;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Attempt;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;

/**
 * Class to manage the creation of learner answers
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class AnswerFactory
{
    /**
     * Create a new Answer entity from a serialized array
     *
     * @param string  $content
     * @param Item    $item
     * @param Attempt $attempt
     *
     * @return Answer
     */
    public static function create($content, Item $item, $attempt = null)
    {
        $answer = new Answer();
        $answer->setContent($content);
        $answer->setItem($item);
        if (!is_null($attempt)) {
            $answer->setAttempt($attempt);
        }

        return $answer;
    }
}
