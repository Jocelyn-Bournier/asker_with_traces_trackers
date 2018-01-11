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

use SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException;

/**
 * Class Inverse
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Inverse extends Expression
{
    /**
     * @const EXPR_NAME = 'inverse'
     */
    const EXPR_NAME = 'inverse';

    /**
     * @var Expression
     */
    private $expression;

    /**
     * Evaluate the result of the operation with the values specified in parameters for the
     * variables.
     *
     * @param array $parameters
     *
     * @throws NotEvaluableException
     * @return int|float
     */
    public function evaluate(array $parameters = array())
    {
        if ($this->expression->evaluate($parameters) == 0) {
            throw new NotEvaluableException('Division by 0');
        }

        return 1 / $this->expression->evaluate($parameters);
    }

    /**
     * Sets the values of the variables
     *
     * @param array $parameters
     */
    public function valuate(array $parameters)
    {
        $this->expression->valuate($parameters);
    }

    /**
     * Return if the expression contains one of the specified variables
     *
     * @param array $varNames
     *
     * @return bool
     */
    public function containsOneOfVariables(array $varNames)
    {
        return $this->expression->containsOneOfVariables($varNames);
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
        if ($this->containsOneOfVariables(array($varName))) {
            return 1;
        }

        return 0;
    }

    /**
     * Set expression
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Expression $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Get expression
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Expression
     */
    public function getExpression()
    {
        return $this->expression;
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
        $expr = $this->expression->getClean();

        if ($expr->getExprName() === Inverse::EXPR_NAME) {
            /** @var Inverse $expr */
            return $expr->getExpression();
        } else {
            $this->expression = $expr;

            return $this;
        }
    }
}
