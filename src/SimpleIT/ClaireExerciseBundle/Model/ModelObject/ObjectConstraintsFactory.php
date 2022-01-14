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

use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectId;

/**
 * Factory that manages the creation of ObjectConstraints
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ObjectConstraintsFactory
{

    /**
     * Creates an ObjectConstraint (for exercise model) from a DomNode
     *
     * @param \DOMNode $node
     * @param int      $type
     *
     * @return ObjectConstraints
     * @throws \Exception
     */
    public static function createFromDomNode(\DOMNode $node, $type = null)
    {
        $oc = new ObjectConstraints();

        // overwrite the type if any is found in the DomNode
        if ($type != null) {
            $oc->setType($type);
        } else {
            $param = $node->attributes
                ->getNamedItem("type");
            if (!is_null($param)) {
                $param = $param->nodeValue;
            }

            $oc->setType($param);
        }

        // object content
        $nodes = $node->childNodes;
        foreach ($nodes as $childNode) {
            // complex nodes (metadata)
            if ($childNode->nodeName == "metadata-constraint") {
                $oc->addMetadataConstraint(
                    MetadataConstraintFactory::createFromDomNode($childNode)
                );
            } // text nodes
            else {
                $nodeTextContent = $childNode->textContent;
                switch ($childNode->nodeName) {
                    case "excluded-resource" :
                        $excluded = new ObjectId();
                        $excluded->setId($nodeTextContent);
                        $oc->addExcluded($excluded);
                        break;
                }
            }
        }

        return $oc;
    }
}
