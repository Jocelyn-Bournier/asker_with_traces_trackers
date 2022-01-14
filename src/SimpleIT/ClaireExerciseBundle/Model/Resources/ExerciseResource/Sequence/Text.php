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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidExerciseResourceException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\SequenceResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Text
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Text extends SequenceElement
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $text;

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
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Validate text
     *
     * @throws InvalidExerciseResourceException
     */
    public function  validate($param = null)
    {
        if ($param != SequenceResource::TEXT) {
            throw new InvalidExerciseResourceException('Only a sequence of manual texts can contain text elements');
        }

        if (is_null($this->text) || $this->text == "") {
            throw new InvalidExerciseResourceException('Text can not be empty in sequence');
        }
    }
}
