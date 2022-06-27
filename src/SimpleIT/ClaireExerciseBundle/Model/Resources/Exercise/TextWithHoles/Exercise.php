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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\TextWithHoles;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextWithHolesResource;
use stdClass;

/**
 * Class Exercise
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Exercise extends CommonExercise
{
    /**
     * @var Item
     * @Serializer\Exclude
     */
    private $item;

    /**
     * Constructor : itemCount = 1 for this type of exercise.
     */
    function __construct($wording)
    {
        parent::__construct($wording);
        $this->itemCount = 1;
        $this->item = array();
    }

    public function addItem($item, $model)
    {
        $newItem = new Item();
        $content = json_decode($item->getContent());

        $newItem->setText($content->text);
        $newItem->setBold($content->bold);
        $newItem->setItalize($content->italize);
        $newItem->setUnderline($content->underline);

        $holes = array();
        $holesWithAnswers = array();

        $coverages = $model->getCoverages();
        foreach ($model->getAnnotationsLists() as $annotationList) {
            foreach ($content->annotations_list as $annotationsLists) {
                if ($annotationList == $annotationsLists->name) {
                    $filteredElements = TextWithHolesResource::filterByConstraint($content->annotations, $annotationsLists->constraint);
                    $coverNb = count($filteredElements);
                    $globalCoverage = count($filteredElements);
                    foreach ($coverages as $coverage) {
                        $findCoverage = false;
                        if(!$coverage['isGlobal'] && $coverage['listName'] == $annotationsLists->name){
                            $findCoverage = true;
                            if($coverage['type'] == "nbElements" && $coverage['value'] <= count($filteredElements)){
                                $coverNb = $coverage['value'];
                            } else {
                                $coverNb = count($filteredElements)*($coverage['value'] / 100.) ;
                            }
                        } else if($coverage['isGlobal']){
                            if($coverage['type'] == "nbElements" && $coverage['value'] <= count($filteredElements)){
                                $globalCoverage = $coverage['value'];
                            } else {
                                $globalCoverage = count($filteredElements)*($coverage['value'] / 100.) ;
                            }
                        }
                        if(!$findCoverage){
                            $coverNb = $globalCoverage;
                        }
                    }

                    $randomFilteredElements = array_rand($filteredElements,$coverNb);
                    foreach($randomFilteredElements as $holeNb){
                        $obj = new stdClass;
                        $obj->resId = $item->getId();
                        $hole = (object) array_merge((array)$filteredElements[$holeNb], (array)$obj);
                        array_push($holes, $hole);
                    }
                }
            }
        }

        foreach ($content->annotations as $annotation) {
            if($annotation->cle == $model->getResponsesTag()){
                foreach($holes as $hole){
                    if($hole->indiceDebut == $annotation->indiceDebut && $hole->indiceFin == $annotation->indiceFin){
                        $obj = new stdClass;
                        $obj->answer = $annotation->valeur;
                        $hole = (object) array_merge((array)$hole, (array)$obj);
                        array_push($holesWithAnswers, $hole);
                    }
                }
            }
        }

        $holesWithIndications = Array();
        if($model->getGenerateIndication()){
                foreach ($content->annotations as $annotation) {
                    if ($annotation->cle == $model->getIndicationKey()) {
                        foreach ($holesWithAnswers as $hole) {
                            if ($hole->indiceDebut == $annotation->indiceDebut && $hole->indiceFin == $annotation->indiceFin) {
                                $obj = new stdClass;
                                $obj->indication = $annotation->valeur;
                                $hole = (object)array_merge((array)$hole, (array)$obj);
                                array_push($holesWithIndications, $hole);
                            }
                        }
                    }
                }

        $newItem->setHoles($holesWithIndications);
        } else {
            $newItem->setHoles($holesWithAnswers);
        }
        $this->item = [$newItem];
    }




    public function getTextWithHoles(){
        return $this->item;
    }

    /**
     * Compute the itemCount
     */
    public function finalize()
    {
        $this->itemCount = 1;
    }
}
