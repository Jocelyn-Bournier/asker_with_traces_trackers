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

namespace SimpleIT\ClaireExerciseBundle\Serializer\Handler;

use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;

/**
 * Custom Handler for abstract class in exercise serialization
 */
class AbstractClassForExerciseHandler implements SubscribingHandlerInterface
{
    /**
     * Get the subscribing methods
     *
     * @return array
     */
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array(
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource',
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise',
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonItem',
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel',
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock',
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject',
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceElement',
                     'SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\CommonKnowledge',
                 ) as $class) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => $class,
                'method'    => 'serializeAbstract',
            );
        }

        return $methods;
    }

    /**
     * Serialize an abstract class
     *
     * @param GenericSerializationVisitor $visitor
     * @param mixed                       $object
     * @param array                       $type
     * @param SerializationContext        $context
     *
     * @return mixed
     */
    public function serializeAbstract(
        GenericSerializationVisitor $visitor,
        $object,
        array $type,
        SerializationContext $context
    )
    {
        return $visitor->getNavigator()->accept(clone($object), null, $context);
    }
}
