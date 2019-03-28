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

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\DomainKnowledge;

use SimpleIT\ApiResourcesBundle\Exception\InvalidKnowledgeException;
use SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Addition;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Cos;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Equation;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Expression;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Inverse;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Multiplication;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Power;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\BitwiseAnd;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Sin;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Value;
use SimpleIT\ClaireExerciseBundle\Model\DomainKnowledge\Formula\Variable;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Unknown;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Variable as ResourceVariable;

/**
 * Service that manages formulas
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class FormulaService implements FormulaServiceInterface
{
    /** @const MAX_LOOPS = 50 */
    const MAX_LOOPS = 50;

    /** @const DIGIT_REPRESENTATION = '*' */
    const DIGIT_REPRESENTATION = '*';

    static $textOperators = array('sin', 'cos');

    /**
     * Check if the syntax of a text equation is correct (brackets, operators, etc.)
     *
     * @param string $equation
     *
     * @return array If no error in the expression
     */
    public function checkTextEquation($equation)
    {
        return $this->splitAndCheckExpression($equation);
    }

    /**
     * Converts a text expression into an expression tree object
     *
     * @param $expression
     *
     * @return Addition|Cos|Equation|Multiplication|Power|Sin|Value|Variable
     */
    public function textExpressionToExpression($expression)
    {
        $splitExpr = $this->splitAndCheckExpression($expression);

        $expr = $this->buildExpr($splitExpr)->getClean();

        return $expr;
    }

    /**
     * Converts a split expression into an expression tree object
     *
     * @param array $splitExpr
     
     * @return Addition|Cos|Equation|Multiplication|Power|Sin|Value|Variable
     * @throws \LogicException
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    private function buildExpr($splitExpr)
    {
        $keys = $this->getRootOperators($splitExpr);
        if (!empty($keys)) {

            if ($splitExpr[$keys[0]] === '=') {
                $expr = new Equation();
                $expr->setLeftExpression(
                    $this->buildExpr(array_slice($splitExpr, 0, $keys[0]))
                );
                $expr->setRightExpression(
                    $this->buildExpr(array_slice($splitExpr, $keys[0] + 1))
                );
            } elseif ($splitExpr[$keys[0]] === '^') {
                $expr = new Power();
                $expr->setLeftExpression(
                    $this->buildExpr(array_slice($splitExpr, 0, $keys[0]))
                );
                $expr->setRightExpression(
                    $this->buildExpr(array_slice($splitExpr, $keys[0] + 1))
                );
            } elseif ($splitExpr[$keys[0]] === '&') {
                $expr = new BitwiseAnd();
                $expr->setLeftExpression(
                    $this->buildExpr(array_slice($splitExpr, 0, $keys[0]))
                );
                $expr->setRightExpression(
                    $this->buildExpr(array_slice($splitExpr, $keys[0] + 1))
                );
            } else {
                if ($splitExpr[$keys[0]] === '*' || $splitExpr[$keys[0]] === '/') {
                    $expr = new Multiplication();
                    // first term
                    $expr->addTerm(
                        $this->buildExpr(array_slice($splitExpr, 0, $keys[0]))
                    );
                } elseif ($splitExpr[$keys[0]] === '+') {
                    $expr = new Addition();
                    // first term
                    $expr->addTerm(
                        $this->buildExpr(array_slice($splitExpr, 0, $keys[0]))
                    );
                } elseif ($splitExpr[$keys[0]] === '-') {
                    $expr = new Addition();
                    // first term
                    if ($keys[0] === 0) {
                        $term = $this->buildExpr(array('0'));
                    } else {
                        $term = $this->buildExpr(array_slice($splitExpr, 0, $keys[0]));
                    }
                    $expr->addTerm($term);
                } else {
                    throw new \LogicException('Unexpected ' . $splitExpr[$keys[0]]);
                }

                // other terms
                for ($i = 0; $i < count($keys); $i++) {
                    // find first and last index for the sub expr
                    $first = $keys[$i] + 1;
                    if ($i === count($keys) - 1) {
                        $length = count($splitExpr) - $first;
                    } else {
                        $length = $keys[$i + 1] - $first;
                    }

                    $term = $this->buildExpr(array_slice($splitExpr, $first, $length));

                    if ($splitExpr[$keys[$i]] === '*' || $splitExpr[$keys[$i]] === '+') {
                        $expr->addTerm($term);
                    } elseif ($splitExpr[$keys[$i]] === '/') {
                        $inverse = new Inverse();
                        $inverse->setExpression($term);
                        $expr->addTerm($inverse);
                    } elseif ($splitExpr[$keys[$i]] === '-') {
                        $expr->addTerm($this->additiveInverse($term));
                    }
                }
            }
        } // if not free sign operators are found
        // Brackets
        elseif ($splitExpr[0] === '(') {
            $last = array_pop($splitExpr);
            if ($last !== ')') {
                throw new \LogicException('Closing bracket expected');
            }
            array_shift($splitExpr);
            $expr = $this->buildExpr($splitExpr);

        } // text operator
        elseif (ereg('^[a-z|A-Z]', $splitExpr[0])) {
            $expr = $this->buildTextOp($splitExpr);
        } // value or variable
        else {
            if (count($splitExpr) !== 1) {
                throw new \LogicException('The must be only one element in array ' .
                print_r($splitExpr, true));
            }
            // value
            if (is_numeric($splitExpr[0])) {
                $expr = new Value();
                $expr->setValue($splitExpr[0]);
            } //variable
            elseif (substr($splitExpr[0], 0, 1) === '$') {
                $expr = new Variable();
                $expr->setName(substr($splitExpr[0], 1));
            } // there should be no else if syntax check is ok
            else {
                throw new \LogicException('Unable to resolve expression ' .
                print_r($splitExpr, true));
            }
        }

        return $expr;
    }

    /**
     * Build the expression that is surrounded by a text operator
     *
     * @param $splitExpr
     *
     * @return Cos|Sin
     * @throws \LogicException
     */
    private function buildTextOp($splitExpr)
    {
        $operator = array_shift($splitExpr);
        switch ($operator) {
            case 'sin':
                $expr = new Sin();
                break;
            case 'cos':
                $expr = new Cos();
                break;
            default:
                throw new \LogicException('Unknown operator: ' . $operator);
        }

        // clean surrounding brackets
        $firstBracket = array_shift($splitExpr);
        $lastBracket = array_pop($splitExpr);
        if ($firstBracket !== '(' || $lastBracket !== ')') {
            throw new \LogicException('Brackets must surround the expression. Processing error.');
        }
        $expr->setExpression($this->buildExpr($splitExpr));

        return $expr;
    }

    /**
     * Split an expression into array and check syntax
     *
     * @param $expression
     *
     * @return array
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException
     */
    private function splitAndCheckExpression($expression)
    {
        // remove spaces
        $expression = str_replace(' ', '', $expression);

        $cbc = str_split($expression);

        $currentType = null;
        $prevType = null;
        $current = '';
        $brackets = array();
        $signEqual = false;

        $split = array();

        foreach ($cbc as $key => $char) {
            // $
            if ($char === '$') {
                $this->addPOE($split, $current, $currentType, $prevType, $key, $expression);
                $currentType = 'var';
                $current = '';
            } // letter
            elseif (ereg('[a-z|A-Z]', $char)) {
                if ($currentType !== 'var' && $currentType !== 'textOp') {
                    $this->addPOE($split, $current, $currentType, $prevType, $key, $expression);
                    $currentType = 'textOp';
                    $current = '';
                }
            } // digit
            elseif (ereg('[0-9]', $char)) {
                if ($currentType !== 'prePoint' && $currentType !== 'postPoint' && $currentType !== 'var'
                ) {
                    $this->addPOE($split, $current, $currentType, $prevType, $key, $expression);
                    $currentType = 'prePoint';
                    $current = '';
                }
            } //point
            elseif ($char === '.') {
                if ($currentType !== 'prePoint') {
                    throw new NotEvaluableException(
                        'Unexpected "." at position ' . $key . ' in expression ' .
                        $expression
                    );
                }
                $currentType = 'postPoint';
            } // operator
            elseif (
                $char === '+' || $char === '-' || $char === '*' ||
                $char === '/' || $char === '^' || $char === '&'
            ) {
                $this->addPOE($split, $current, $currentType, $prevType, $key, $expression);
                $currentType = 'op';
                $current = '';
            } // =
            elseif ($char === '=') {
                if (!empty($brackets)) {
                    throw new NotEvaluableException('Invalid brackets in expression ' . $expression);
                }
                if ($signEqual) {
                    throw new NotEvaluableException('More than one = in equation ' . $expression);
                }
                $signEqual = true;
                $this->addPOE($split, $current, $currentType, $prevType, $key, $expression);
                $currentType = 'eq';
                $current = '';
            } // bracket
            elseif ($char === '(') {
                $this->addPOE($split, $current, $currentType, $prevType, $key, $expression);
                $currentType = 'opBracket';
                $current = '';

                // bracket check
                $brackets[] = '(';
            } elseif ($char === ')') {
                $this->addPOE($split, $current, $currentType, $prevType, $key, $expression);
                $currentType = 'clBracket';
                $current = '';

                // bracket check
                $bracket = array_pop($brackets);
                if ($bracket !== '(') {
                    throw new NotEvaluableException(
                        'Unexpected "' . $char . '" at position ' . $key . ' in expression ' .
                        $expression
                    );
                }
            } // invalid char
            else {
                throw new NotEvaluableException(
                    'Unexpected "' . $char . '" at position ' . $key . ' in expression ' .
                    $expression
                );
            }
            $current .= $char;
        }
        $this->addPOE($split, $current, $currentType, $prevType, count($cbc) - 1, $expression);

        if (!empty($brackets)) {
            throw new NotEvaluableException('Invalid brackets in expression ' . $expression);
        }

        return $split;
    }

    /**
     * Add a piece of expression at the end of an expression
     *
     * @param $split
     * @param $current
     * @param $currentType
     * @param $prevType
     * @param $pos
     * @param $expr
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException
     */
    private function addPOE(&$split, $current, $currentType, &$prevType, $pos, $expr)
    {
        if ($current !== '') {
            // sign operator or ')'
            if ($currentType === 'op' || $current === ')') {
                if (
                    $current !== '-' && $prevType !== 'var' && $prevType !== 'prePoint' &&
                    $prevType !== 'postPoint' && $split[count($split) - 1] != ')'
                ) {
                    throw new NotEvaluableException(
                        'Unexpected ' . $current . ' at position ' . $pos . ' in expression ' . $expr
                    );
                }
            } // text operator, value, variable
            elseif (
                $currentType === 'textOp' || $currentType === 'prePoint' ||
                $currentType === 'postPoint' || $currentType === 'var'
            ) {
                if (
                    $prevType !== 'op' && $prevType !== null && $prevType !== 'eq' &&
                    $split[count($split) - 1] != '('
                ) {
                    throw new NotEvaluableException(
                        'Unexpected ' . $current . ' at position ' . $pos . ' in expression ' . $expr
                    );
                }
            } elseif ($current === '(') {
                if (
                    $prevType !== 'op' && $prevType !== null && $prevType !== 'eq' &&
                    $split[count($split) - 1] != '(' && $prevType !== 'textOp'
                ) {
                    throw new NotEvaluableException(
                        'Unexpected ' . $current . ' at position ' . $pos . ' in expression ' . $expr
                    );
                }
            }

            if ($currentType === 'textOp') {
                if (array_search($current, self::$textOperators) === false) {
                    throw new NotEvaluableException(
                        'Unknown operator ' . $current . ' at position ' . $pos . ' in expression '
                        . $expr
                    );
                }
            }

            // Empty var name
            if ($current === '$') {
                throw new NotEvaluableException("Variable name cannot be empty");
            }

            $split[] = $current;
        }
        $prevType = $currentType;
    }

    /**
     * Get the more root operators in the expression
     *
     * @param $splitExpr
     *
     * @return array
     */
    private function getRootOperators($splitExpr)
    {
        $bestFound = null;
        $brackets = 0;
        $opKeys = array();

        foreach ($splitExpr as $key => $part) {
            if ($brackets > 0) {
                switch ($part) {
                    case  '(':
                        $brackets++;
                        break;
                    case ')' :
                        $brackets--;
                        break;
                }
            } else {
                switch ($part) {
                    case  '(':
                        $brackets++;
                        break;
                    case '=' :
                        $bestFound = '=';
                        $opKeys = array($key);
                        break(2);
                    case '+' :
                    case '-' :
                        if ($bestFound === '+') {
                            $opKeys[] = $key;
                        } else {
                            $bestFound = '+';
                            $opKeys = array($key);
                        }
                        break;
                    case '*' :
                    case '/' :
                        if ($bestFound === '*') {
                            $opKeys[] = $key;
                        } elseif ($bestFound !== '+') {
                            $bestFound = '*';
                            $opKeys = array($key);
                        }
                        break;
                    case '^' :
                        if ($bestFound === '^') {
                            $opKeys[] = $key;
                        } elseif ($bestFound !== '+' && $bestFound !== '*') {
                            $bestFound = '^';
                            $opKeys = array($key);
                        }
                        break;
                    case '&' :
                        if ($bestFound === '&') {
                            $opKeys[] = $key;
                        } elseif ($bestFound !== '+' && $bestFound !== '*') {
                            $bestFound = '&';
                            $opKeys = array($key);
                        }
                        break;
                }
            }
        }

        return $opKeys;
    }

    /**
     * Resolve an equation for the specified variable
     *
     * @param Equation $equation
     * @param string   $varName
     *
     * @return Equation
     * @throws \Exception
     */
    public function resolveEquation(Equation $equation, $varName)
    {
        if ($varName == null) {
            throw new NotEvaluableException('The unknown must be specified');
        }

        $loopCount = 0;

        $left = $equation->getLeftExpression();
        $right = $equation->getRightExpression();

        while (
            !(
                $left->getExprName() === Variable::EXPR_NAME
                && $left->getName() === $varName
                && !$right->containsOneOfVariables(array($varName))
            )
            && $loopCount < self::MAX_LOOPS) {
            $loopCount++;

            $branchWithVariable = $left->countBranchWithVariable($varName)
                + $right->countBranchWithVariable($varName);

            // if no branch with variable, impossible to resolve the equation
            if ($branchWithVariable === 0) {
                throw new NotEvaluableException('The variable ' . $varName .
                ' is not found in the equation. Unable to resolve.');
            } // if only one branch with the variable, classic resolution
            elseif ($branchWithVariable === 1) {
                // Determine var side
                if ($left->countBranchWithVariable($varName) > 0) {
                    $varSide = $left;
                    $noVarSide = $right;
                } else {
                    $varSide = $right;
                    $noVarSide = $left;
                }

                // if multiplication or addition
                if ($this->isAdditionOrMultiplication($varSide)) {
                    $this->reverseOperation($varSide, $noVarSide, $varName);
                } // if inverse
                elseif ($varSide->getExprName() === Inverse::EXPR_NAME) {
                    /** @var Inverse $varSide */
                    $this->reverseInverse($varSide, $noVarSide);
                } else {
                    throw new \Exception('Unable to resolve this type of expression');
                }

                $left = $varSide;
                $right = $noVarSide;

            } // else (many var branches)
            else {
                // throw new \Exception(print_r($right));
                // if variables are in multiplication or addition root,
                //try distribution-factorization method
                if (
                    (
                        (
                            $this->isAdditionOrMultiplication($left)
                            ||
                            (
                                $left->getExprName() === Variable::EXPR_NAME &&
                                $left->getName() === $varName
                            )
                        )
                        || !$left->countBranchWithVariable($varName) > 0
                    )
                    &&
                    (
                        (
                            $this->isAdditionOrMultiplication($right)
                            ||
                            (
                                $right->getExprName() === Variable::EXPR_NAME &&
                                $right->getName() === $varName
                            )
                        )
                        || !$right->countBranchWithVariable($varName) > 0
                    )
                ) {
                    $this->distributionFactorization($left, $right, $varName);
                } else {
                    throw new \Exception('Loop ' . $loopCount .
                    ': Unable to resolve this type of expression with multiple ' .
                    'use of the variable');
                }
            }
        }

        if ($loopCount === self::MAX_LOOPS) {
            throw new NotEvaluableException('The equation cannot be resolved. Max loops reached');
        }

        $equation->setLeftExpression($left);
        $equation->setRightExpression($right);

        return $equation;
    }

    /**
     * Reverse addition and multiplication to isolate a variable
     *
     * @param Expression $varSide
     * @param Expression $noVarSide
     * @param string     $varName
     */
    private function reverseOperation(
        Expression &$varSide,
        Expression &$noVarSide,
        $varName
    )
    {
        $this->getVarAndNoVarTerms($varTerms, $noVarTerms, $varSide, $varName);

        // inverse the no var terms
        if ($varSide->getExprName() === Addition::EXPR_NAME) {
            $this->inverseAdditionTerms($noVarTerms);
        } else {
            $this->inverseMultiplicationTerms($noVarTerms);
        }

        // update the equation
        $this->updateEquation(
            $varSide,
            $noVarSide,
            $noVarTerms,
            $varTerms
        );
    }

    /**
     * Split an expression into 2 arrays: var terms and no var terms
     *
     * @param null       $varTerms
     * @param null       $noVarTerms
     * @param Expression $expr
     * @param string     $varName
     * @param array      $operationsToSplit Root operations for which terms must be split
     *
     * @internal param array $terms
     */
    private function getVarAndNoVarTerms(
        &$varTerms,
        &$noVarTerms,
        Expression $expr,
        $varName,
        array $operationsToSplit = array(Addition::EXPR_NAME, Multiplication::EXPR_NAME)
    )
    {
        $varTerms = array();
        $noVarTerms = array();

        if ($this->isAdditionOrMultiplication($expr) &&
            array_search($expr->getExprName(), $operationsToSplit, true) !== false
        ) {
            /** @var Addition|Multiplication $expr */
            $terms = $expr->getTerms();

            /** @var Expression $term */
            foreach ($terms as $term) {
                if ($term->containsOneOfVariables(array($varName))) {
                    $varTerms[] = $term;
                } else {
                    $noVarTerms[] = $term;
                }
            }
        } else {
            if ($expr->containsOneOfVariables(array($varName))) {
                $varTerms[] = $expr;
            } else {
                $noVarTerms[] = $expr;
            }
        }
    }

    /**
     * Reverse an equation in which var side is an inverse
     *
     * @param Inverse    $varSide
     * @param Expression $noVarSide
     */
    private function reverseInverse(Inverse &$varSide, Expression &$noVarSide)
    {
        $varSide = $varSide->getExpression();
        $newInverse = new Inverse();
        $newInverse->setExpression($noVarSide);
        $noVarSide = $newInverse;
    }

    /**
     * Reorganize varSide and no varSide by moving the no var terms from the var side to the no
     * var side.
     *
     * @param Expression   $varSide
     * @param Expression   $noVarSide
     * @param Expression[] $noVarTerms
     * @param Expression[] $varTerms
     */
    private function updateEquation(
        Expression &$varSide,
        Expression &$noVarSide,
        array $noVarTerms,
        array $varTerms
    )
    {
        $operation = $varSide->getExprName();
        // put them no var side
        if ($noVarSide->getExprName() === $operation) {
            /** @var Addition|Multiplication $noVarSide */
            foreach ($noVarTerms as $noVarTerm) {
                $noVarSide->addTerm($noVarTerm);
            }
        } else {
            $newNoVarSide = Expression::createOperation($operation);
            $newNoVarSide->setTerms($noVarTerms);
            $newNoVarSide->addTerm($noVarSide);
            $noVarSide = $newNoVarSide;
        }

        // update var side
        if (count($varTerms) === 1) {
            $varSide = $varTerms[0];
        } else {
            $varSide = Expression::createOperation($operation);
            $varSide->setTerms($varTerms);
        }
    }

    /**
     * Inverse addition terms by multiplicating them by -1
     *
     * @param $terms
     */
    private function inverseAdditionTerms(
        &$terms
    )
    {
        /** @var Expression $term */
        foreach ($terms as &$term) {
            $term = $this->additiveInverse($term);
        }
    }

    /**
     * Inverse multiplication terms by placing them in an inverse object
     *
     * @param $terms
     */
    private function inverseMultiplicationTerms(
        &$terms
    )
    {
        /** @var Expression $term */
        foreach ($terms as &$term) {
            $inverse = new Inverse();
            $inverse->setExpression($term);
            $term = $inverse;
        }
    }

    /**
     * Method of the distribution factorization to solve certain equations
     *
     * @param Expression $left
     * @param Expression $right
     * @param string     $varName
     *
     * @throws \LogicException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException
     */
    private function distributionFactorization(Expression &$left, Expression &$right, $varName)
    {
        $left = $left->distributeMultiplication($varName);
        $right = $right->distributeMultiplication($varName);

        // Split additively
        $this->getVarAndNoVarTerms(
            $leftVarTerms,
            $leftNoVarTerms,
            $left,
            $varName,
            array(Addition::EXPR_NAME)
        );
        $this->getVarAndNoVarTerms(
            $rightVarTerms,
            $rightNoVarTerms,
            $right,
            $varName,
            array(Addition::EXPR_NAME)
        );

        // var side
        $this->inverseAdditionTerms($rightVarTerms);
        $varTerms = array_merge($leftVarTerms, $rightVarTerms);

        if (count($varTerms) < 2) {
            throw new \LogicException('In distribution factorization method, there must be at ' .
            'least 2 terms depending on the variable.');
        }

        // no var side
        $this->inverseAdditionTerms($leftNoVarTerms);
        $noVarTerms = array_merge($leftNoVarTerms, $rightNoVarTerms);
        if (count($noVarTerms) === 0) {
            $noVarSide = new Value();
            $noVarSide->setValue(0);
        } elseif (count($noVarTerms) === 1) {
            $noVarSide = $noVarTerms[0];
        } else {
            $noVarSide = new Addition();
            $noVarSide->setTerms($noVarTerms);
        }

        // Factorization of the var side
        /** @var Expression $varTerm */
        foreach ($varTerms as &$varTerm) {
            if ($varTerm->getExprName() === Variable::EXPR_NAME) {
                /** @var Value $varTerm */
                $varTerm = new Value();
                $varTerm->setValue(1);
            } elseif ($varTerm->getExprName() === Multiplication::EXPR_NAME) {
                /** @var Multiplication $varTerm */
                if ($varTerm->countBranchWithVariable($varName) !== 1) {
                    throw new NotEvaluableException('This equation cannot be resolved');
                }

                // remove one instance of the variable in each multiplication (factorization)
                $multiplicationTerms = array();
                /** @var Expression $term */
                foreach ($varTerm->getTerms() as $term) {
                    if (!$term->containsOneOfVariables(array($varName))) {
                        $multiplicationTerms[] = $term;
                    } elseif ($term->getExprName() !== Variable::EXPR_NAME) {
                        throw new NotEvaluableException(
                            'This type of equation cannot be resolved'
                        );
                    }
                }

                $varTerm->setTerms($multiplicationTerms);
            } else {
                throw new \LogicException('This term should be a multiplication or a variable');
            }
        }

        // right side (no var side) is the no var side divided by the (factorized) var terms
        $denominator = new Addition();
        $denominator->setTerms($varTerms);
        $inverse = new Inverse();
        $inverse->setExpression($denominator);

        if ($noVarSide->getExprName() === Multiplication::EXPR_NAME) {
            $noVarSide->addTerm($inverse);
        } else {
            $multiplication = new Multiplication();
            $multiplication->addTerm($noVarSide);
            $multiplication->addTerm($inverse);
            $noVarSide = $multiplication;
        }

        $right = $noVarSide;

        // left side is the variable
        $left = new Variable();
        $left->setName($varName);
    }

    /**
     * Tells if an expression is addition or multiplication or not.
     *
     * @param Expression $expression
     *
     * @return bool
     */
    private function isAdditionOrMultiplication(Expression $expression)
    {
        return $expression->getExprName() === Addition::EXPR_NAME
        || $expression->getExprName() === Multiplication::EXPR_NAME;
    }

    /**
     * Changes the sign of an expression (multiplication by -1)
     *
     * @param Expression $expr
     *
     * @return Multiplication
     */
    private function additiveInverse(Expression $expr)
    {
        $minusOne = new Value();
        $minusOne->setValue(-1);

        if ($expr->getExprName() === Multiplication::EXPR_NAME) {
            /** @var Multiplication $expr */
            $expr->addTerm($minusOne);
        } else {
            $multiplication = new Multiplication();
            $multiplication->addTerm($minusOne);
            $multiplication->addTerm($expr);
            $expr = $multiplication;
        }

        return $expr;
    }

    /**
     * Instantiate variables from an array of resource variables.
     *
     * @param ResourceVariable[] $resourceVariables The input resource variables
     *
     * @return array An array in wich keys are variable names
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException
     */
    public function instantiateVariables(array $resourceVariables)
    {
        $outputValues = array();
        $count = count($resourceVariables);
        do {
            $notInstantiated = array();
            /** @var ResourceVariable $resourceVariable */
            foreach ($resourceVariables as $resourceVariable) {
                $value = $this->instantiateVariable($resourceVariable, $outputValues);
                if ($value === false) {
                    $notInstantiated[] = $resourceVariable;
                } else {
                    $outputValues[$resourceVariable->getName()] = $value;
                }
            }
            $resourceVariables = $notInstantiated;
            if (count($resourceVariables) === $count && $count > 0) {
                $varNames = array();
                /** @var ResourceVariable $resVar */
                foreach ($resourceVariables as $resVar) {
                    $varNames[] = $resVar->getName();
                }
                throw new NotEvaluableException(
                    'Some variables cannot be instantiated: ' . implode(',', $varNames)
                );
            }
            $count = count($resourceVariables);
        } while ($count != 0);

        return $outputValues;
    }

    /**
     * Instantiate one variable depending on the already instantiated ones.
     *
     * @param ResourceVariable $resourceVariable
     * @param array            $variables
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NotEvaluableException
     * @throws \SimpleIT\ApiResourcesBundle\Exception\InvalidKnowledgeException
     * @return int|float|boolean
     */
    private function instantiateVariable(ResourceVariable $resourceVariable, array $variables)
    {
        if ($resourceVariable->getValueType() === Formula\Variable::VALUES &&
        count($resourceVariable->getValues()) > 0) {
            $value = $resourceVariable->getValues()[array_rand($resourceVariable->getValues())];
        } elseif ($resourceVariable->getValueType() === Formula\Variable::INTERVAL &&
            $resourceVariable->getInterval() !== null) {
            $step = $resourceVariable->getInterval()->getStep();
            $min = $resourceVariable->getInterval()->getMin();
            $max = $resourceVariable->getInterval()->getMax();

            if ($resourceVariable->getType() === ResourceVariable::INTEGER) {
                if ($step != null && $step != 0) {
                    $value = mt_rand(0, (int)(($max - $min) / $step)) * $step + $min;
                } else {
                    $value = mt_rand($min, $max);
                }
            } else {
                if ($resourceVariable->getDigitsAfterPoint() === null) {
                    throw new NotEvaluableException('Number of digits after point missing.');
                }

                if ($resourceVariable->getType() === ResourceVariable::FLOAT) {
                    if ($step != null && $step !== 0) {
                        $value = round(
                            mt_rand(0, (int)(($max - $min) / $step)) * $step + $min,
                            $resourceVariable->getDigitsAfterPoint()
                        );
                    } else {
                        $value = mt_rand(
                                $min * pow(10, $resourceVariable->getDigitsAfterPoint()),
                                $max * pow(10, $resourceVariable->getDigitsAfterPoint())
                            ) / pow(10, $resourceVariable->getDigitsAfterPoint()) * 100;
                    }
                } elseif ($resourceVariable->getType() === ResourceVariable::SCIENTIFIC) {
                    $R = mt_rand(0, mt_getrandmax()) / mt_getrandmax();
                    $value = exp(
                        $R * log($resourceVariable->getInterval()->getMax())
                        + (1 - $R) * log($resourceVariable->getInterval()->getMin())
                    );

                    $value = $this->roundSignificantDigits(
                        $value,
                        $resourceVariable->getDigitsAfterPoint() + 1
                    );
                } else {
                    throw new InvalidKnowledgeException('Type of variable must be specified.');
                }
            }
        } elseif ($resourceVariable->getValueType() === Formula\Variable::EXPRESSION &&
            $resourceVariable->getExpression() !== null) {
            $value = $this->instantiateVariableWithExpression($resourceVariable, $variables);
        } else {
            $value = false;
        }

        return $value;
    }

    /**
     * Instantiate a variable with an expression
     *
     * @param ResourceVariable $resourceVariable A resource variable containing an expression
     * @param array            $variables
     *
     * @return int
     */
    private function instantiateVariableWithExpression(
        ResourceVariable $resourceVariable,
        array $variables
    )
    {
        $expression = $this->textExpressionToExpression($resourceVariable->getExpression());

        try {
            $value = $expression->evaluate($variables);
        } catch (NotEvaluableException $nee) {
            return false;
        }

        if ($resourceVariable->getType() === ResourceVariable::INTEGER) {
            $value = round($value);
        } else {
            if ($resourceVariable->getDigitsAfterPoint() !== null) {
                if ($resourceVariable->getType() === ResourceVariable::FLOAT) {
                    $value = round($value, $resourceVariable->getDigitsAfterPoint());
                } elseif ($resourceVariable->getType() === ResourceVariable::SCIENTIFIC) {
                    $value = $this->roundSignificantDigits(
                        $value,
                        $resourceVariable->getDigitsAfterPoint() + 1
                    );
                }
            }
        }

        return $value;
    }

    /**
     * Round a number with the right number of significant digits
     *
     * @param $number
     * @param $significantDigits
     *
     * @return float
     */
    private function roundSignificantDigits($number, $significantDigits)
    {
        $multiplier = 1;
        while ($number < 0.1) {
            $number *= 10;
            $multiplier /= 10;
        }
        while ($number >= 1) {
            $number /= 10;
            $multiplier *= 10;
        }

        return round($number, $significantDigits) * $multiplier;
    }

    /**
     * Resolve a formula and returns values of the variables in an array
     *
     * @param string                                                                            $formula
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula\Variable[] $variables
     * @param Unknown                                                                           $unknown
     *
     * @throws \SimpleIT\ApiResourcesBundle\Exception\InvalidKnowledgeException
     * @return array
     */
    public function resolveFormulaResource($formula, $variables, $unknown)
    {
        $unknownName = $unknown->getName();
        $equation = $this->textExpressionToExpression($formula);
        if ($equation->getExprName() !== Equation::EXPR_NAME) {
            throw new InvalidKnowledgeException('The formula is not an equation');
        }
        $values = $this->instantiateVariables($variables);
        $unknownValue = $this->resolveEquation($equation, $unknownName)->evaluate($values);

        // format value
        if ($unknown->getType() === ResourceVariable::INTEGER) {
            if (!is_integer($unknownValue)) {
                throw new \LogicException('The computed answer is not an integer' . $unknownValue);
            }
        } elseif ($unknown->getType() === ResourceVariable::FLOAT) {
            if ($unknown->getDigitsAfterPoint() > 0) {
                $unknownValue = round($unknownValue, $unknown->getDigitsAfterPoint());
            }
        } elseif ($unknown->getType() === ResourceVariable::SCIENTIFIC) {
            if ($unknown->getDigitsAfterPoint() > 0) {
                $unknownValue = $this->roundSignificantDigits(
                    $unknownValue,
                    $unknown->getDigitsAfterPoint() + 1
                );
            }
        }

        $values[$unknownName] = $unknownValue;

        return $values;
    }

    /**
     * Get the format (mask) for a value
     *
     * @param $value
     *
     * @return null|string
     */
    public function getValueFormat($value)
    {
        if (is_numeric($value)) {
            $format = '';
            foreach (str_split($value) as $char) {
                if (is_numeric($char)) {
                    $format .= self::DIGIT_REPRESENTATION;
                } else {
                    $format .= $char;
                }
            }
        } else {
            $format = null;
        }

        return $format;
    }

    /**
     * Build an array of (unique) formats for an array of values
     *
     * @param $values
     *
     * @return array|null
     */
    public function getValueArrayFormat($values)
    {
        if (is_array($values)) {
            $formats = array();

            foreach ($values as $value) {
                $formats[] = $this->getValueFormat($value);
            }

            $formats = array_filter(array_unique($formats));
        } else {
            $formats = null;
        }

        return $formats;
    }

    /**
     * Validate a formula resource
     *
     * @param Formula $content
     *
     * @return bool
     * @throws \SimpleIT\ApiResourcesBundle\Exception\InvalidKnowledgeException
     */
    public function validateFormulaResource(Formula $content)
    {
        try {
            $this->checkTextEquation($content->getEquation());
            // resolve the equation once to detect errors
            $this->resolveFormulaResource(
                $content->getEquation(),
                $content->getVariables(),
                $content->getUnknown()
            );
        } catch (NotEvaluableException $nee) {
            throw new InvalidKnowledgeException('Equation is not valid: ' . $nee->getMessage());
        }

        return true;
    }

    /**
     * Prefix the variable names with the formula name
     *
     * @param array  $variables An array in which keys are variable names and values the values
     * @param string $formulaName
     *
     * @return array
     */
    public function prefixVariableNames($variables, $formulaName)
    {
        $returnVariables = array();
        foreach ($variables as $key => $variable) {
            $returnVariables[$formulaName . ':' . $key] = $variable;
        }

        return $returnVariables;
    }
}
