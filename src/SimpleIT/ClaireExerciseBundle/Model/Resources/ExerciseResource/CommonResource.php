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
use SimpleIT\ClaireExerciseBundle\Model\Resources\Validable;

/**
 * Class ResourceResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 * @Serializer\Discriminator(field = "object_type", map = {
 *    "picture": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\PictureResource",
 *    "document": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\DocumentResource",
 *    "text": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextResource",
 *    "sequence": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\SequenceResource",
 *    "multiple_choice_question": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\MultipleChoiceQuestionResource",
 *    "open_ended_question": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OpenEndedQuestionResource"
 * })
 */
abstract class CommonResource implements Validable
{
    /**
     * @const PICTURE = "picture"
     */
    const PICTURE = "picture";

    /**
     * @const DOCUMENT = "document"
     */
    const DOCUMENT = "document";

    /**
     * @const TEXT = "text"
     */
    const TEXT = "text";

    /**
     * @const MULTIPLE_CHOICE_QUESTION = "multiple-choice-question"
     */
    const MULTIPLE_CHOICE_QUESTION = "multiple-choice-question";

    /**
     * @const OPEN_ENDED_QUESTION = "open-ended-question"
     */
    const OPEN_ENDED_QUESTION = "open-ended-question";

    /**
     * @const SEQUENCE = "sequence"
     */
    const SEQUENCE = "sequence";

    /**
     * @var array An array of LocalFormula
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\LocalFormula>")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    protected $formulas;

    /**
     * Set formulas
     *
     * @param array $formulas
     */
    public function setFormulas($formulas)
    {
        $this->formulas = $formulas;
    }

    /**
     * Get formulas
     *
     * @return array
     */
    public function getFormulas()
    {
        return $this->formulas;
    }

    /**
     * Checks if a type of resource is valid
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isValidType($type)
    {
        if (
            $type === self::TEXT
            || $type === self::SEQUENCE
            || $type === self::PICTURE
            || $type === self::DOCUMENT
            || $type === self::MULTIPLE_CHOICE_QUESTION
            || $type === self::OPEN_ENDED_QUESTION
        ) {
            return true;
        }

        return false;
    }
}
