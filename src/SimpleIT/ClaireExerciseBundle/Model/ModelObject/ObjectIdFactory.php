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

namespace SimpleIT\ClaireExerciseBundle\Model\ModelObject;

use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectId;

/**
 * Factory that manages the creation of ObjectId
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ObjectIdFactory
{

    /**
     * Creates an ObjectId (for exercise model) from a DomNode
     *
     * @param \DOMNode $node
     *
     * @return ObjectId
     */
    public static function createFromDomNode(\DOMNode $node)
    {
        $oId = new ObjectId();

        // copy id
        $oId->setId($node->textContent);

        // return the object
        return $oId;
    }

    /**
     * Creates an ObjectId (for exercise model) from an id as it can be retrieved
     * from metadata value
     *
     * @param int $id
     *
     * @return ObjectId
     */
    public static function createFromResourceId($id)
    {
        $oId = new ObjectId();
        $oId->setId($id);

        return $oId;
    }
}
