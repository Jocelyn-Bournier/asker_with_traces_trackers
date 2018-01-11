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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Validable;

/**
 * Class CommonKnowledge
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 * @Serializer\Discriminator(field = "object_type", map = {
 *    "formula": "SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula"
 * })
 */
abstract class CommonKnowledge implements Validable
{
    /**
     * @const FORMULA = "formula"
     */
    const FORMULA = "formula";

    /**
     * Checks if a type of knowledge is valid
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isValidType($type)
    {
        if (
            $type === self::FORMULA
        ) {
            return true;
        }

        return false;
    }
}
