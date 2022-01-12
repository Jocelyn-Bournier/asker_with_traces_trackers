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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SequenceResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class SequenceResource extends CommonResource
{
    /**
     * @const MANUAL_TEXT = "text"
     */
    const MANUAL_TEXT = "text";

    /**
     * @const OBJECTS = "objects"
     */
    const OBJECTS = "objects";

    /**
     * @const SLICED_TEXT = "sliced_text"
     */
    const SLICED_TEXT = "sliced_text";

    /**
     * @var string $sequenceType
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "resource_storage", "resource_list", "owner_resource_list"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $sequenceType;

    /**
     * @var int The id of the text that is sliced in the sequence. Null if no text
     * @Serializer\Type("int")
     * @Serializer\Groups({"details", "resource_storage", "resource_storage"})
     */
    private $textObjectId;

    /**
     * @var SequenceBlock
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock")
     * @Serializer\Groups({"details", "resource_storage", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     * @Assert\Valid
     */
    private $mainBlock;

    /**
     * Set mainBlock
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock $mainBlock
     */
    public function setMainBlock($mainBlock)
    {
        $this->mainBlock = $mainBlock;
    }

    /**
     * Get mainBlock
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock
     */
    public function getMainBlock()
    {
        return $this->mainBlock;
    }

    /**
     * Set textObjectId
     *
     * @param int $textObjectId
     */
    public function setTextObjectId($textObjectId)
    {
        $this->textObjectId = $textObjectId;
    }

    /**
     * Get textObjectId
     *
     * @return int
     */
    public function getTextObjectId()
    {
        return $this->textObjectId;
    }

    /**
     * Validate the sequence resource
     */
    public function  validate($param = null)
    {
        if ($this->mainBlock === null) {
            throw new InvalidExerciseResourceException('A sequence must conatin at least one block');
        }

        if ($this->sequenceType == self::SLICED_TEXT &&
            ($this->textObjectId === null || $this->textObjectId = '')
        ) {
            throw new InvalidExerciseResourceException('A sliced text must be linked with a text');
        }
        $this->mainBlock->validate($this->sequenceType);
    }

    /**
     * Set sequenceType
     *
     * @param string $sequenceType
     */
    public function setSequenceType($sequenceType)
    {
        $this->sequenceType = $sequenceType;
    }

    /**
     * Get sequenceType
     *
     * @return string
     */
    public function getSequenceType()
    {
        return $this->sequenceType;
    }
}
