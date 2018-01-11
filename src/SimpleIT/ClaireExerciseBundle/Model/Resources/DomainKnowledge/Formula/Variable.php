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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Variable, is used in a formula
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Variable extends Unknown
{
    const INTERVAL = 'interval';

    const VALUES = 'values';

    const EXPRESSION = 'expression';

    /**
     * @var array $valueType
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $valueType;

    /**
     * @var Interval $interval
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Interval")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $interval;

    /**
     * @var array $values
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $values;

    /**
     * @var string $expression
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $expression;

    /**
     * @var array $wrongValues
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $wrongValues;

    /**
     * @var array $wrongExpressions
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "knowledge_storage", "resource_storage", "exercise_model_storage"})
     */
    private $wrongExpressions;

    /**
     * Set expression
     *
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Get expression
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Set interval
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Interval $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * Get interval
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Interval
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set values
     *
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * Get values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set wrongExpressions
     *
     * @param array $wrongExpressions
     */
    public function setWrongExpressions($wrongExpressions)
    {
        $this->wrongExpressions = $wrongExpressions;
    }

    /**
     * Get wrongExpressions
     *
     * @return array
     */
    public function getWrongExpressions()
    {
        return $this->wrongExpressions;
    }

    /**
     * Set wrongValues
     *
     * @param array $wrongValues
     */
    public function setWrongValues($wrongValues)
    {
        $this->wrongValues = $wrongValues;
    }

    /**
     * Get wrongValues
     *
     * @return array
     */
    public function getWrongValues()
    {
        return $this->wrongValues;
    }

    /**
     * Set valueType
     *
     * @param array $valueType
     */
    public function setValueType($valueType)
    {
        $this->valueType = $valueType;
    }

    /**
     * Get valueType
     *
     * @return array
     */
    public function getValueType()
    {
        return $this->valueType;
    }
}
