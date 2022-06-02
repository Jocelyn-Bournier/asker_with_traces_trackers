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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;

/**
 * Class Exercise
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Exercise extends CommonExercise
{
    /**
     * @var Item
     * @Serializer\Exclude
     */
    private $item;

    /**
     * @var sharedTags
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "item"})
     */
    private $sharedTags;

    /**
     * @var $sharedConstraints
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "corrected", "item"})
     */
    private $sharedConstraints;

    /**
     * Constructor : itemCount = 1 for this type of exercise.
     */
    function __construct($wording)
    {
        parent::__construct($wording);
        $this->itemCount = 1;
    }

    public function setSharedConstraints($sharedConstraints)
    {
        $this->sharedConstraints = $sharedConstraints;
    }

    public function setSharedTags($sharedTags)
    {
        $this->sharedTags = $sharedTags;
    }
}
