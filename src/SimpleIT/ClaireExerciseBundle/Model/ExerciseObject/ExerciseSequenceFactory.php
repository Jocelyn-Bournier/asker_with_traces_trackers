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

namespace SimpleIT\ClaireExerciseBundle\Model\ExerciseObject;

use SimpleIT\ClaireExerciseBundle\Exception\InvalidExerciseResourceException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseSequenceObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\ResourceId;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceBlock;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\SequenceElement;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\Text;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\Sequence\TextFragment;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\SequenceResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextResource;

/**
 * Factory to create ExerciseSequenceObject
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ExerciseSequenceFactory
{
    /**
     * Create ExerciseSequenceObject from ExerciseResource
     *
     * @param SequenceResource $sequenceRes
     * @param array            $requiredResources
     *
     * @throws \LogicException
     * @return ExerciseSequenceObject
     */
    public static function createFromCommonResource(
        SequenceResource $sequenceRes,
        array $requiredResources
    )
    {
        $sequence = new ExerciseSequenceObject();
        $objects = array();
        $id = 0;

        // type of sequence (from objects or from a text or a sequence itself)
        $type = $sequenceRes->getSequenceType();

        // get the text if sliced text
        $textResource = null;
        if ($type == SequenceResource::SLICED_TEXT) {
            $textResource = $requiredResources[$sequenceRes->getTextObjectId()];
        }

        // build the structure
        $structure = self::translateBlock(
            $requiredResources,
            $sequenceRes->getMainBlock(),
            $objects,
            $id,
            $type,
            $textResource
        );

        $sequence->setObjects($objects);
        $sequence->setStructure($structure);

        return $sequence;
    }

    /**
     * Translate a node and its content (recursively).
     *
     * @param array             $required
     * @param SequenceElement   $sequenceElement
     * @param array             $objects
     * @param int               $id
     * @param string            $type
     * @param TextResource|null $textResource
     *
     * @return array
     */
    private
    static function translateBlock(
        $required,
        SequenceElement $sequenceElement,
        &$objects,
        &$id,
        $type,
        $textResource
    )
    {
        $struct = array();

        $struct['name'] = self::getElementName($sequenceElement);

        if (get_class($sequenceElement) === SequenceElement::BLOCK_CLASS) {
            /** @var SequenceBlock $sequenceElement */
            foreach ($sequenceElement->getElements() as $el) {
                /** @var SequenceElement $sequenceElement */
                // recursive call if other block
                $struct[] = self::
                    translateBlock(
                        $required,
                        $el,
                        $objects,
                        $id,
                        $type,
                        $textResource
                    );
            }
        } // add the element
        else {
            $obj = null;

            switch (get_class($sequenceElement)) {
                case SequenceElement::TEXT_FRAGMENT_CLASS:
                    /** @var TextFragment $sequenceElement */
                    // get the text fragment
                    $text = $textResource->getText();
                    $start = $sequenceElement->getStart();
                    $end = $sequenceElement->getEnd();
                    $frag = substr($text, $start, $end - $start);

                    // create a text object from the fragment
                    $obj = ExerciseTextFactory::createFromText($frag);

                    break;

                case SequenceElement::RESOURCE_ID_CLASS:
                    /** @var ResourceId $sequenceElement */
                    // get resource id
                    $id = $sequenceElement->getResourceId();

                    // get the resource
                    $obj = ExerciseObjectFactory::createExerciseObject($required[$id]);

                    break;

                case SequenceElement::TEXT_CLASS:
                    /** @var Text $sequenceElement */
                    // get the text
                    $frag = $sequenceElement->getText();

                    // create a text object from the fragment
                    $obj = ExerciseTextFactory::createFromText($frag);
                    break;

                default:
                    break;
            }

            // add this object to the list and to the solution
            if (!is_null($obj)) {
                $objects[$id] = $obj;
                $struct[] = $id;
                $id++;
            }
        }

        return $struct;
    }

    /**
     * get the name of the sequence element
     *
     * @param SequenceElement $el
     *
     * @return string
     * @throws InvalidExerciseResourceException
     */
    static private function getElementName(SequenceElement $el)
    {
        switch (get_class($el)) {
            case SequenceElement::BLOCK_CLASS:
                /** @var SequenceBlock $el */
                $name = $el->getBlockType();
                break;
            case SequenceElement::RESOURCE_ID_CLASS:
                $name = "el";
                break;
            case SequenceElement::TEXT_CLASS:
                $name = "el";
                break;
            case SequenceElement::TEXT_FRAGMENT_CLASS:
                $name = "el";
                break;
            default:
                throw new InvalidExerciseResourceException('Unknown type of sequence element');
        }

        return $name;
    }
}
