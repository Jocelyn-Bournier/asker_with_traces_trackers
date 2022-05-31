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

    /**
     * Validate text resource
     *
     * @throws InvalidExerciseResourceException
     */
    public function  validate($param = null)
    {
        if (is_null($this->text) || $this->text == '') {
            throw new InvalidExerciseResourceException('Invalid Text with holes resource');
        }
    }
}
