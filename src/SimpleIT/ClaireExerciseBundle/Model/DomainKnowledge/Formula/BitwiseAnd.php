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

namespace SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula;

/**
 * Class BitwiseAnd
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class BitwiseAnd extends Expression
{
    /**
     * @const EXPR_NAME = 'bitwiseand'
     */
    const EXPR_NAME = 'bitwiseand';

    /** @var Expression */
    private $leftExpression;

    /** @var Expression */
    private $rightExpression;

    /**
     * Evaluate the result of the operation with the values specified in parameters for the
     * variables.
     *
     * @param array $parameters
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException
     * @return int|float
     */
    public function evaluate(array $parameters = array())
    {
        $left = $this->leftExpression->evaluate($parameters);
        $right = $this->rightExpression->evaluate($parameters);

        //if ($right < 0 && $left == 0) {
        //    throw new NotEvaluableException('Power exponant negative and number = 0');
        //}
        return $this->leftExpression->evaluate($parameters) & $this->rightExpression->evaluate($parameters);
        return pow(
            $this->leftExpression->evaluate($parameters),
            $this->rightExpression->evaluate($parameters)
        );
    }

    /**
     * Sets the values of the variables
     *
     * @param array $parameters
     */
    public function valuate(array $parameters)
    {
        $this->leftExpression->valuate($parameters);
        $this->rightExpression->valuate($parameters);
    }

    /**
     * return if the expression contains one of the specified variables
     *
     * @param array $varNames
     *
     * @return bool
     */
    public function containsOneOfVariables(array $varNames)
    {
        $contains = $this->leftExpression->containsOneOfVariables($varNames) ||
            $this->rightExpression->containsOneOfVariables($varNames);

        return $contains;
    }

    /**
     * Checks if exactly one branch of the expression contains the variable
     *
     * @param $varName
     *
     * @return int
     */
    public function countBranchWithVariable($varName)
    {
        $count = 0;
        if ($this->leftExpression->containsOneOfVariables(array($varName))) {
            $count++;
        }
        if ($this->rightExpression->containsOneOfVariables(array($varName))) {
            $count++;
        }

        return $count;
    }

    /**
     * Set leftExpression
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Expression $leftExpression
     */
    public function setLeftExpression($leftExpression)
    {
        $this->leftExpression = $leftExpression;
    }

    /**
     * Get leftExpression
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Expression
     */
    public function getLeftExpression()
    {
        return $this->leftExpression;
    }

    /**
     * Set rightExpression
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Expression $rightExpression
     */
    public function setRightExpression($rightExpression)
    {
        $this->rightExpression = $rightExpression;
    }

    /**
     * Get rightExpression
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Expression
     */
    public function getRightExpression()
    {
        return $this->rightExpression;
    }

    /**
     * Get the expression name
     *
     * @return string
     */
    public function getExprName()
    {
        return self::EXPR_NAME;
    }

    /**
     * Distributes the multiplication over the addition
     *
     * @param string $varName
     *
     * @return Expression
     */
    public function distributeMultiplication($varName)
    {
        return $this;
    }

    /**
     * Get a clean version of the expression
     *
     * @return Expression
     */
    public function getClean()
    {
        $left = $this->leftExpression->getClean();
        $right = $this->rightExpression->getClean();

        if ($left->getExprName() === Value::EXPR_NAME && $right->getExprName() === Value::EXPR_NAME
        ) {
            /**
             * @var Value $left
             * @var Value $right
             */
            $value = new Value();
            $value->setValue(pow($left->getValue(), $right->getValue()));

            return $value;
        } else {
            $this->leftExpression = $left;
            $this->rightExpression = $right;

            return $this;
        }

    }
}
