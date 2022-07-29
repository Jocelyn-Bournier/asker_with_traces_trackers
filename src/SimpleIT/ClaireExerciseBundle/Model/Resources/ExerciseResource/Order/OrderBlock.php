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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Order;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrderBlock
 *
 */
class OrderBlock
{
    /**
     * @var string $rule
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $rule;

    /**
     * @var array $rules
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private $rules = array();

    /**
     * @var array $positions
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $positions = array();

    /**
     * @var array $items
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Order\OrderItem>")
     * @Serializer\Groups({"details", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $items = array();

    public function getRule(){
        return $this->rule;
    }

    public function getItems(){
        return $this->items;
    }
    
    public function getRules(){
        return $this->rules;
    }

    public function getPositions(){
        return $this->positions;
    }
}
