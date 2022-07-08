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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidExerciseResourceException;
use stdClass;

/**
 * Class TextResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TextWithHolesResource extends CommonResource
{
    /**
     * @var string $text The text
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private string $text = '';

    /**
     * @var array $bold Bold elements
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private array $bold = [];

    /**
     * @var array $italize Italize elements
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private array $italize = [];

    /**
     * @var array $annotations Annotations elements
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private array $annotations = [];

    /**
     * @var array $annotationsList AnnotationsList elements
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private array $annotationsList = [];

    /**
     * @var array $errorsList ErrorsList elements
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private array $errorsList = [];

    /**
     * @var array $underline Underlined elements
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_storage"})
     */
    private array $underline = [];

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return array
     */
    public function getBold()
    {
        return $this->bold;
    }

    /**
     * Set text
     *
     * @param array $bold
     */
    public function setBold($bold)
    {
        $this->bold = $bold;
    }

    /**
     * Get text
     *
     * @return array
     */
    public function getUnderline()
    {
        return $this->underline;
    }

    /**
     * Set text
     *
     * @param array $underline
     */
    public function setUnderline($underline)
    {
        $this->underline = $underline;
    }

    /**
     * Get text
     *
     * @return array
     */
    public function getItalize()
    {
        return $this->italize;
    }

    /**
     * Set text
     *
     * @param array $italize
     */
    public function setItalize($italize)
    {
        $this->italize = $italize;
    }

    /**
     * Get text
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Set text
     *
     * @param array $annotations
     */
    public function setAnnotations($annotations)
    {
        $this->annotations = $annotations;
    }

    /**
     * Get text
     *
     * @return array
     */
    public function getAnnotationsList()
    {
        return $this->annotationsList;
    }

    /**
     * Set text
     *
     * @param array $annotationsList
     */
    public function setAnnotationsList($annotationsList)
    {
        $this->annotationsList = $annotationsList;
    }

    /**
     * Get text
     *
     * @return array
     */
    public function getErrorsList()
    {
        return $this->errorsList;
    }

    /**
     * Set text
     *
     * @param array $errorsList
     */
    public function setErrorsList(array $errorsList)
    {
        $this->errorsList = $errorsList;
    }

    public function filterElement(string $annotationName, $responseTag = null, $indicationTag = null){
        $filteredElement = Array();
        if ($responseTag == null){
            $filteredElement['answers'] = null;
        }
        if ($indicationTag == null){
            $filteredElement['indications'] = null;
        }
        $constraint = Array();
        foreach($this->annotationsList as $annotationsConstraint){
            if($annotationsConstraint['name'] == $annotationName){
                $constraint = $annotationsConstraint['constraint'];
            }
        }

        return $filteredElement;
    }


    public function filterByConstraint($annotations, $constraint){
        if ($constraint->cle != null){
            if($constraint->valeur == ""){
                $tabCle = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->cle == $constraint->cle;}));
                return $tabCle;
                } else {
                $tabCle = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->cle == $constraint->cle;}));
                $tabValeur = array();
                $tabCleValeur = array();
                $tabValeurToRemove = array();
                    switch ($constraint->operateur){
                        case "=" :
                            $tabValeur = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->cle == $constraint->cle && $annotation->valeur == $constraint->valeur;}));
                            break;
                        case "≠" :
                            $tabValeur = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->valeur != $constraint->valeur;}));
                            $tabValeurToRemove = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->valeur == $constraint->valeur;}));
                            break;
                        case "<" :
                            $tabValeur = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->cle == $constraint->cle && $annotation->valeur < $constraint->valeur;}));
                            break;
                        case ">" :
                            $tabValeur = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->cle == $constraint->cle && $annotation->valeur > $constraint->valeur;}));
                            break;
                        case "≤" :
                            $tabValeur = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->cle == $constraint->cle && $annotation->valeur <= $constraint->valeur;}));
                            break;
                        case "≥" :
                            $tabValeur = array_filter($annotations,(function ($annotation) use ($constraint) {return $annotation->cle == $constraint->cle && $annotation->valeur >= $constraint->valeur;}));
                            break;

                    }
                    foreach($tabCle as $cle) {
                        foreach($tabValeur as $valeur){
                            if($valeur->indiceDebut == $cle->indiceDebut && $valeur->indiceFin == $cle->indiceFin){
                                if(array_search($cle, $tabCleValeur) === -1 ){array_push($tabCleValeur, $cle);}
                                if(array_search($valeur, $tabCleValeur) === -1){array_push($tabCleValeur,$valeur);}
                            }
                        }
                    }
                    foreach ($annotations as $annotation){
                        foreach ($tabValeur as $valeur){
                            if(array_search($annotation, $tabCleValeur) === -1 && $annotation->indiceDebut == $valeur->indiceDebut && $annotation->indiceFin == $valeur->indiceFin) {
                                array_push($tabCleValeur, $annotation);
                            }
                        }
                    }
                    if ($tabValeurToRemove != array()){
                        foreach($tabValeurToRemove as $valeur){
                            $tabCleValeur = array_filter($tabCleValeur,(function ($annotation) use ($valeur) {return $annotation->indiceDebut != $valeur->indiceDebut || $annotation->indiceFin != $valeur->indiceFin;}));
                        }
                    }
                    return $tabCleValeur;
                }
        } else if ($constraint->operateur == "ET") {
            return $this->filterByConstraint($this->filterByConstraint($annotations, $constraint->filsGauche), $constraint->filsDroit);

        } else if ($constraint->operateur == "OU") {
            $left = $this->filterByConstraint($annotations, $constraint->filsGauche);
            $right = $this->filterByConstraint($annotations, $constraint->filsDroit);
                foreach($right as $elem){
                    if(array_search($elem, $left) === -1){
                        array_push($left, $elem);
                    }
                }
                return $left;

            } else if ($constraint->operateur == "OU EXCLUSIF") {
            return;
        }
    }

    private function getConstraint($listName): ? array {
        foreach ($this->annotationsList as $annotation){
            if ($annotation->name == $listName){
                return $annotation->constraint;
            }
        }
        return null;
    }

    /**
     * Validate text resource
     *
     * @throws InvalidExerciseResourceException
     */
    public function  validate($param = null)
    {
        echo "toto";
        if (is_null($this->text) || $this->text == '') {
            throw new InvalidExerciseResourceException('A text is needed');
        }

        $errorOnAnnotations = $this->checkAnnotationsListHolesGeneration();
        if (count($errorOnAnnotations) > 0){
            throw new InvalidExerciseResourceException('Following annotations lists can\'t be used to generate holes : ' . json_encode($errorOnAnnotations));
        }
    }

    public function checkAnnotationsListHolesGeneration() {

        $annotations = [];
        foreach ($this->getAnnotations() as $annotation){
            $annotations[] = (object) $annotation;
        }

        foreach($this->getAnnotationsList() as $annotationList) {
            $filteredElements = $this->filterByConstraint($annotations, (object) $annotationList['constraint']);
            $nbHoles = count($filteredElements);
            if ($nbHoles == 0) {
                return false;
            }
        }
        return true;
    }

    private function ToObject($Array) {

        // Create new stdClass object
        $object = new stdClass();

        // Use loop to convert array into
        // stdClass object
        foreach ($Array as $key => $value) {
            if (is_array($value)) {
                $value = $this->ToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

}
