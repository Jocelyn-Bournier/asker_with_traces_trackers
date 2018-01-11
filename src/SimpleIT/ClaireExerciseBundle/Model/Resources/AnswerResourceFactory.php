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

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Answer;
use SimpleIT\ClaireExerciseBundle\Serializer\Handler\AbstractClassForExerciseHandler;

/**
 * Class AnswerResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class AnswerResourceFactory
{

    /**
     * Create an AnswerResource collection
     *
     * @param array $answers
     *
     * @return array
     */
    public static function createCollection(array $answers)
    {
        $answerResources = array();
        foreach ($answers as $answer) {
            $answerResources[] = self::create($answer);
        }

        return $answerResources;
    }

    /**
     * Create an AnswerResource
     *
     * @param Answer $answer
     *
     * @return AnswerResource
     */
    public static function create(Answer $answer)
    {
        $class = 'SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource';
        $serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerSubscribingHandler(new AbstractClassForExerciseHandler());
                }
            )
            ->build();

        /** @var AnswerResource $answerResource */
        $answerResource = $serializer->deserialize($answer->getContent(), $class, 'json');

        $answerResource->setId($answer->getId());
        $answerResource->setMark($answer->getMark());

        return $answerResource;
    }
}
