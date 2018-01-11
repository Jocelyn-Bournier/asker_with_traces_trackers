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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\ResourceBlock;

/**
 * Block of questions in a multiple choice model
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class QuestionBlock extends ResourceBlock
{
    /**
     * @var int $maxNumberOfPropositions The max number of propositions in each question
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $maxNumberOfPropositions;

    /**
     * @var int $maxNOfRightPropositions The max number of right propositions in each question
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $maxNumberOfRightPropositions;

    /**
     * QuestionBlock constructor
     *
     * @param int $numberOfOccurrences
     * @param int $maxNumberOfPropositions
     * @param int $maxNOfRightPropositions
     *
     * @return QuestionBlock
     */
    function __construct(
        $numberOfOccurrences,
        $maxNumberOfPropositions = null,
        $maxNOfRightPropositions = null
    )
    {
        $this->numberOfOccurrences = $numberOfOccurrences;
        $this->maxNumberOfPropositions = $maxNumberOfPropositions;
        $this->maxNumberOfRightPropositions = $maxNOfRightPropositions;
    }

    /**
     * Get the maximum number of propositions
     *
     * @return int
     */
    public function getMaxNumberOfPropositions()
    {
        return $this->maxNumberOfPropositions;
    }

    /**
     * Get the maximum number of propositions
     *
     * @param     $maxNumberOfPropositions
     */
    public function setMaxNumberOfPropositions($maxNumberOfPropositions)
    {
        $this->maxNumberOfPropositions = $maxNumberOfPropositions;
    }

    /**
     * get th emaximum number of right propositions
     *
     * @return int
     */
    public function getMaxNumberOfRightPropositions()
    {
        return $this->maxNumberOfRightPropositions;
    }

    /**
     * Set the maximum number of right propositions
     *
     * @param int $maxNOfRightPropositions
     */
    public function setMaxNumberOfRightPropositions($maxNOfRightPropositions)
    {
        $this->maxNumberOfRightPropositions = $maxNOfRightPropositions;
    }
}
