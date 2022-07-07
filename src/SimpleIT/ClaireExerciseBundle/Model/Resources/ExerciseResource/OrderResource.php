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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Order\OrderBlock;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrderResource
 */
class OrderResource extends CommonResource
{
    /**
     * @var OrderBlock
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Order\OrderBlock")
     * @Serializer\Groups({"details", "resource_storage", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     * @Assert\Valid
     */
    private $block;

    /**
     * Set block
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Order\OrderBlock $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * Get block
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Order\OrderBlock
     */
    public function getblock()
    {
        return $this->block;
    }

    /**
     * Validate the order resource
     */
    public function validate($param = null)
    {
        if ($this->block === null) {
            throw new InvalidExerciseResourceException('An order must contain at least one block');
        }

        $this->block->validate();
    }
}
