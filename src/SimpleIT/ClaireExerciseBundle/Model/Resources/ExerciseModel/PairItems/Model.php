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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;

/**

/**
 * Class Model
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Model extends CommonModel
{
    /**
     * @var array $pairBlocks An array of PairBlock
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\PairBlock>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $pairBlocks = array();

    /**
     * Get pair blocks
     *
     * @return array
     */
    public function getPairBlocks()
    {
        return $this->pairBlocks;
    }

    /**
     * Add a PairBlock to the model
     *
     * @param PairBlock $pairBlock
     */
    public function addPairBlock(PairBlock $pairBlock)
    {
        $this->pairBlocks[] = $pairBlock;
    }

    /**
     * Set pairBlocks
     *
     * @param array $pairBlocks
     */
    public function setPairBlocks($pairBlocks)
    {
        $this->pairBlocks = $pairBlocks;
    }
}
