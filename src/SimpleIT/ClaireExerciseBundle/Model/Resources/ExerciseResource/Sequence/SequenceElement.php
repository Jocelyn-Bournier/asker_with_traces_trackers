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
use SimpleIT\ClaireExerciseBundle\Model\Resources\Validable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SequenceElement
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 * @Serializer\Discriminator(field = "object_type", map = {
 *    "block": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock",
 *    "resource_id": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\ResourceId",
 *    "text": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\Text",
 *    "text_fragment": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\TextFragment"
 * })
 */
abstract class SequenceElement implements Validable
{
    /**
     * @const BLOCK_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock'
     */
    const BLOCK_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock';

    /**
     * @const RESOURCE_ID_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\ResourceId'
     */
    const RESOURCE_ID_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\ResourceId';

    /**
     * @const TEXT_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\Text'
     */
    const TEXT_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\Text';

    /**
     * @const TEXT_FRAGMENT_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\TextFragment'
     */
    const TEXT_FRAGMENT_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\TextFragment';
}
