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
 * Class TextFragment
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TextFragment extends SequenceElement
{
    /**
     * @var int The position in the text of the beginning of the fragment
     * @Serializer\Type("int")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $start;

    /**
     * @var int The position in the text of the end of the fragment
     * @Serializer\Type("int")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $end;

    /**
     * Set end
     *
     * @param int $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end
     *
     * @return int
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set start
     *
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start
     *
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Validate the text fragement
     *
     * @throws InvalidExerciseResourceException
     */
    public function  validate($param = null)
    {
        if ($param != SequenceResource::SLICED_TEXT) {
            throw new InvalidExerciseResourceException('Only a sliced text sequence can contain text fragments');
        }
        if ($this->start === null || $this->end === null || $this->end < $this->start) {
            throw new InvalidExerciseResourceException('Invalid text fragment start or end');
        }
    }
}
