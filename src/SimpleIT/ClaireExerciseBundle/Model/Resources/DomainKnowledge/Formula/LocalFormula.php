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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class LocalFormula
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class LocalFormula
{
    /**
     * @var string $equation
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $equation;

    /**
     * @var int $formulaId
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $formulaId;

    /**
     * @var array $variables
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Variable>")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $variables = array();

    /**
     * @var Unknown $unknown
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Unknown")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $unknown;

    /**
     * @var string $name The name of the formula inside the exercise model or inside the resource
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $name;

    /**
     * Set equation
     *
     * @param string $equation
     */
    public function setEquation($equation)
    {
        $this->equation = $equation;
    }

    /**
     * Get equation
     *
     * @return string
     */
    public function getEquation()
    {
        return $this->equation;
    }

    /**
     * Set formulaId
     *
     * @param int $formulaId
     */
    public function setFormulaId($formulaId)
    {
        $this->formulaId = $formulaId;
    }

    /**
     * Get formulaId
     *
     * @return int
     */
    public function getFormulaId()
    {
        return $this->formulaId;
    }

    /**
     * Set variables
     *
     * @param array $variables
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }

    /**
     * Get variables
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set unknown
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Unknown $unknown
     */
    public function setUnknown($unknown)
    {
        $this->unknown = $unknown;
    }

    /**
     * Get unknown
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Unknown
     */
    public function getUnknown()
    {
        return $this->unknown;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
