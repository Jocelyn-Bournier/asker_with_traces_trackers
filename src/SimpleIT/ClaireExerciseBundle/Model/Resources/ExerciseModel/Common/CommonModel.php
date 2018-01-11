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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ModelDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract class for the exercise models.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 * @Serializer\Discriminator(field = "exercise_model_type", map = {
 *    "group-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Model",
 *    "pair-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\Model",
 *    "order-items": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\Model",
 *    "multiple-choice": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\Model",
 *    "open-ended-question": "SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\Model"
 * })
 */
abstract class CommonModel
{
    /**
     * @const MULTIPLE_CHOICE = "multiple-choice"
     */
    const MULTIPLE_CHOICE = "multiple-choice";

    /**
     * @const GROUP_ITEMS = "group-items"
     */
    const GROUP_ITEMS = "group-items";

    /**
     * @const ORDER_ITEMS = "order-items"
     */
    const ORDER_ITEMS = "order-items";

    /**
     * @const PAIR_ITEMS = "pair-items"
     */
    const PAIR_ITEMS = "pair-items";

    /**
     * @const OPEN_ENDED_QUESTION = "open-ended-question"
     */
    const OPEN_ENDED_QUESTION = "open-ended-question";

    /**
     * @var string The wording
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage", "owner_exercise_model_list"})
     */
    protected $wording;

    /**
     * @var array An array of ModelDocument
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ModelDocument>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    protected $documents = array();

    /**
     * @var array An array of LocalFormula
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\LocalFormula>")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    protected $formulas;

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
     * @return array An array of ModelDocument
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set documents
     *
     * @param array $documents An array of ModelDocument
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }

    /**
     * Add a document
     *
     * @param ModelDocument $document
     */
    public function addDocument(ModelDocument $document)
    {
        $this->documents[] = $document;
    }

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
}
