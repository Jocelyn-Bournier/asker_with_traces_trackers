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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrderedBlock
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class SequenceBlock extends SequenceElement
{
    /**
     * @const DISORDERED = "di"
     */
    const DISORDERED = "di";

    /**
     * @const ORDERED = "or"
     */
    const ORDERED = "or";

    /**
     * @var string Takes for value "or" for an ordered block and "di" for a disordered one
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $blockType;

    /**
     * @var array $elements The content of this block
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceElement>")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $elements;

    /**
     * Set elements
     *
     * @param array $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * Get elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Set blockType
     *
     * @param string $blockType
     */
    public function setBlockType($blockType)
    {
        $this->blockType = $blockType;
    }

    /**
     * Get blockType
     *
     * @return string
     */
    public function getBlockType()
    {
        return $this->blockType;
    }

    /**
     * Validate sequence block
     *
     * @throws InvalidExerciseResourceException
     */
    public function  validate($param = null)
    {
        if ($this->blockType != "or" && $this->blockType != "di") {
            throw new InvalidExerciseResourceException('Invalid type of block in sequence');
        }

        if (is_null($this->elements) || count($this->elements) < 1) {
            throw new InvalidExerciseResourceException('Invalid content for block in sequence: not enough elements');
        }

        foreach ($this->elements as $element) {
            /** @var SequenceElement $element */
            $element->validate($param);
        }
    }
}
