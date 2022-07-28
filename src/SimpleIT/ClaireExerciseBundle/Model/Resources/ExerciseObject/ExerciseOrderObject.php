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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OrderResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An order object in an exercise.
 */
class ExerciseOrderObject extends ExerciseObject
{
    const OBJECT_TYPE = "order";

    /**
     * @var OrderResource
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OrderResource")
     * @Serializer\Groups({"details", "resource_storage", "resource_storage"})
     * @Assert\NotBlank(groups={"create"})
     * @Assert\Valid
     */
    private $orderResource;

    public function getOrderResource(){
        return $this->orderResource;
    }

    public function setOrderResource($orderResource){
        //echo json_encode($orderResource->getBlock()->getItems()[0]);
        $this->orderResource = $orderResource;
    }

}
