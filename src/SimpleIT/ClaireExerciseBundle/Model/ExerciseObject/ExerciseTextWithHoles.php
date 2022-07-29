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

use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;

/**
 * An ExerciseTextWithHoles is the representation of a text with holes
 * retrieved from a resource. The text is not under a final form that can be
 * presented in an exercise.
 * An ExerciseTextWithHoles can contain more holes that will be used in the
 * exercise. The maximum number of holes to be used is specified in
 * the parameters of the ExerciseTextWithHoles.
 *
 * @author Valentin Lachand-Pascal <valentin.lachand-pascal@liris.cnrs.fr>
 */
class ExerciseTextWithHoles extends ExerciseObject
{
    const OBJECT_TYPE = "text-with-holes";

    /**
     * @var string $text The text
     */
    private string $text = '';

    /**
     * @var array $bold Bold elements
     */
    private array $bold = [];

    /**
     * @var array $italize Italize elements
     */
    private array $italize = [];

    /**
     * @var array $annotations Annotations elements
     */
    private array $annotations = [];

    /**
     * @var array $annotationsList AnnotationsList elements
     */
    private array $annotationsList = [];

    /**
     * @var array $errorsList ErrorsList elements
     */
    private array $errorsList = [];

    /**
     * @var array $underline Underlined elements
     */
    private array $underline = [];

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * @param string $question
     */
    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function getBold(): array
    {
        return $this->bold;
    }

    /**
     * @param array $bold
     */
    public function setBold(array $bold): void
    {
        $this->bold = $bold;
    }

    /**
     * @return array
     */
    public function getItalize(): array
    {
        return $this->italize;
    }

    /**
     * @param array $italize
     */
    public function setItalize(array $italize): void
    {
        $this->italize = $italize;
    }

    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * @param array $annotations
     */
    public function setAnnotations(array $annotations): void
    {
        $this->annotations = $annotations;
    }

    /**
     * @return array
     */
    public function getAnnotationsList(): array
    {
        return $this->annotationsList;
    }

    /**
     * @param array $annotationsList
     */
    public function setAnnotationsList(array $annotationsList): void
    {
        $this->annotationsList = $annotationsList;
    }

    /**
     * @return array
     */
    public function getErrorsList(): array
    {
        return $this->errorsList;
    }

    /**
     * @param array $errorsList
     */
    public function setErrorsList(array $errorsList): void
    {
        $this->errorsList = $errorsList;
    }

    /**
     * @return array
     */
    public function getUnderline(): array
    {
        return $this->underline;
    }

    /**
     * @param array $underline
     */
    public function setUnderline(array $underline): void
    {
        $this->underline = $underline;
    }


}
