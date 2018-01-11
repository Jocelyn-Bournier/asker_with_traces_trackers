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

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Attempt;

/**
 * Class AttemptResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class AttemptResourceFactory
{

    /**
     * Create an AttemptResourceFactory collection
     *
     * @param array $attempts
     *
     * @return array
     */
    public static function createCollection(array $attempts)
    {
        $attemptResources = array();
        foreach ($attempts as $attempt) {
            $attemptResources[] = self::create($attempt);
        }

        return $attemptResources;
    }

    /**
     * Create an AttemptResource
     *
     * @param Attempt $attempt
     * @param bool    $links
     *
     * @return AttemptResource
     */
    public static function create(Attempt $attempt, $links = false)
    {
        $attemptResource = new AttemptResource();
        $attemptResource->setId($attempt->getId());

        if (is_null($attempt->getTestAttempt())) {
            $attemptResource->setTestAttempt(null);
        } else {
            $attemptResource->setTestAttempt($attempt->getTestAttempt()->getId());
        }
        $attemptResource->setExercise($attempt->getExercise()->getId());

        if (is_null($attempt->getUser())) {
            $attemptResource->setUser(null);
        } else {
            $attemptResource->setUser($attempt->getUser()->getId());
        }

        $attemptResource->setPosition($attempt->getPosition());

        if ($links) {
            $answers = array();
            foreach ($attempt->getAnswers() as $answer) {
                $answers[] = AnswerResourceFactory::create($answer);
            }
            $attemptResource->setAnswers($answers);
        }

        return $attemptResource;
    }
}
