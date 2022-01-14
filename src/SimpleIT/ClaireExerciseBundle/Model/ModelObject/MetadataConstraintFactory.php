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

namespace SimpleIT\ClaireExerciseBundle\Model\ModelObject;

use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint;

/**
 * Factory that manages the creation of MetadataConstraint
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class MetadataConstraintFactory
{
    /**
     * Create a MetadataConstraint from a metadata-constraint node
     *
     * @param \DOMNode $node
     *
     * @return MetadataConstraint
     * @throws \Exception
     */
    public static function createFromDomNode(\DOMNode $node)
    {
        // Get the key
        $param = $node->attributes
            ->getNamedItem("key")
            ->nodeValue;

        // New MetadataConstraint
        $metadataConstraint = new MetadataConstraint();
        $metadataConstraint->setKey($param);

        // Get constraint list
        $domConstraints = $node->childNodes;

        foreach ($domConstraints as $constr) {
            /*
             *  composed node (between)
             */
            if ($constr->nodeName == "between") {
                $min = null;
                $max = null;

                // Get min and max
                foreach ($constr->childNodes as $minOrMax) {
                    if ($minOrMax->nodeName == "min") {
                        $min = $minOrMax->textContent;
                    } elseif ($minOrMax->nodeName == "max") {
                        $max = $minOrMax->textContent;
                    }
                }

                // validate data
                if ($min == null || $max == null) {
                    throw new \LogicException("Missing between min or max");
                } elseif ($min > $max) {
                    throw new \LogicException("Invalid between : min > max");
                }

                // add to the MetadataConstraint
                $metadataConstraint->setBetween($min, $max);
            }

            /*
             *  empty node (exists)
             */
            if ($constr->nodeName == "exists") {
                $metadataConstraint->setExists();
            } /*
             *  simple value (equals, lt, lte, gt, gte)
             */
            else {
                $nodeTextContent = $constr->textContent;

                // equals
                if ($constr->nodeName == "equals") {
                    $metadataConstraint->addValue($nodeTextContent);
                } // simple comparison
                elseif ($constr->nodeName == "lt" ||
                    $constr->nodeName == "lte" ||
                    $constr->nodeName == "gt" ||
                    $constr->nodeName == "gte"
                ) {
                    $metadataConstraint->setComparison(
                        $constr->nodeName,
                        $nodeTextContent
                    );
                }
            }
        }

        return $metadataConstraint;
    }
}
