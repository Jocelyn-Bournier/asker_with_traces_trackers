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
 * Class Equation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Equation extends Expression
{
    /**
     * @const EXPR_NAME = 'equation'
     */
    const EXPR_NAME = 'equation';

    /**
     * @var Expression
     */
    private $leftExpression;

    /**
     * @var Expression
     */
    private $rightExpression;

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
     * Evaluate the right part of the equation if the left part is a variable.
     *
     * @param array $parameters
     *
     * @throws \LogicException
     * @return int|float
     */
    public function evaluate(array $parameters = array())
    {
        if (get_class($this->leftExpression) !==
            'SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Variable'
        ) {
            throw new \LogicException('The equation has not been resolved.' .
            'Impossible to evaluate the solution.');
        }

        return $this->rightExpression->evaluate($parameters);
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
     * @throws \LogicException
     * @return Expression
     */
    public function distributeMultiplication($varName)
    {
        throw new \LogicException('An equation cannot be distributed by multiplication');
    }

    /**
     * Get a clean version of the expression
     *
     * @return Expression
     */
    public function getClean()
    {
        $this->leftExpression = $this->leftExpression->getClean();
        $this->rightExpression = $this->rightExpression->getClean();

        return $this;
    }
}
