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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidExerciseResourceException;

/**
 * Class TextResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TextResource extends CommonResource
{
    /**
     * @var string $text The text
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private $text;

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Validate text resource
     *
     * @throws InvalidExerciseResourceException
     */
    public function  validate($param = null)
    {
        if (is_null($this->text) || $this->text == '') {
            throw new InvalidExerciseResourceException('Invalid Text resource');
        }
    }
}
