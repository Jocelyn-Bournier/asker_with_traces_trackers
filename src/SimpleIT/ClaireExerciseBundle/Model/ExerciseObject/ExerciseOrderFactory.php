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

use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseOrderObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Order\OrderBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OrderResource;

/**
 * This class manages the creation of instances of ExerciseOrder.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class OrderFactory
{
    /**
     * Create a model question from an exerciseResource
     *
     * @param OrderResource $res The resource
     *
     * @return ExerciseOrderObject
     */
    public static function createFromCommonResource(OrderResource $res)
    {
        $order = new ExerciseOrderObject();

        return $order;
    }
}
