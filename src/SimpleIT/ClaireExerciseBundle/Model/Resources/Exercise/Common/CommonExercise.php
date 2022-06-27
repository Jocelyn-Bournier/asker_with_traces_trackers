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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;

/**
 * Abstract class for the exercises in their final form.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 * @Serializer\Discriminator(field = "exercise_type", map = {
 *    "group-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\GroupItems\Exercise",
 *    "pair-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\PairItems\Exercise",
 *    "order-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OrderItems\Exercise",
 *    "multiple-choice": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice\Exercise",
 *    "open-ended-question": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\OpenEndedQuestion\Exercise",
 *    "text-with-holes": "SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles\Exercise"
 * })
 */
abstract class CommonExercise
{
    /**
     * @const MULTIPLE_CHOICE = 'multiple-choice'
     */
    const MULTIPLE_CHOICE = 'multiple-choice';

    /**
     * @const PAIR_ITEMS = 'pair-items'
     */
    const PAIR_ITEMS = 'pair-items';

    /**
     * @const GROUP_ITEMS = 'group-items'
     */
    const GROUP_ITEMS = 'group-items';

    /**
     * @const ORDER_ITEMS = 'order-items'
     */
    const ORDER_ITEMS = 'order-items';

    /**
     * @const OPEN_ENDED_QUESTION = 'open-ended-question'
     */
    const OPEN_ENDED_QUESTION = 'open-ended-question';

    /**
     * @const TEXT_WITH_HOLES = 'text-with-holes'
     */
    const TEXT_WITH_HOLES = 'text-with-holes';

    /**
     * @var string $wording The wording
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise", "exercise_storage"})
     */
    protected $wording;

    /**
     * @var array $documents An array of ExerciseObject
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject>")
     * @Serializer\Groups({"details", "exercise", "exercise_storage"})
     */
    protected $documents = array();

    /**
     * @var integer $itemCount The number of items in the exercise
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "exercise", "exercise_storage"})
     */
    protected $itemCount;

    /**
     * Construct a new exercise with its wording
     *
     * @param string $wording
     */
    public function __construct($wording = null)
    {
        $this->wording = $wording;
    }

    /**
     * Get wording
     *
     * @return string
     */
    public function getWording()
    {
        return $this->wording;
    }

    /**
     * Set wording
     *
     * @param string $wording
     */
    public function setWording($wording)
    {
        $this->wording = $wording;
    }

    /**
     * Get documents
     *
     * @return array An array of ExerciseObject
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set documents
     *
     * @param array $documents An array of Exercise Objects
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }

    /**
     * Add a document
     *
     * @param ExerciseObject $document
     */
    public function addDocument(ExerciseObject $document)
    {
        $this->documents[] = $document;
    }

    /**
     * Set itemCount
     *
     * @param int $itemCount
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount = $itemCount;
    }

    /**
     * Get itemCount
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }
}
