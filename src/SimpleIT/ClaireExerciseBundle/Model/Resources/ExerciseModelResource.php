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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use JMS\Serializer\Annotation as Serializer;
use SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\Common\CommonExercise;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ExerciseModelResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseModelResource extends SharedResource
{
    /**
     * @const RESOURCE_NAME = 'Exercise Model'
     */
    const RESOURCE_NAME = 'Exercise Model';

    /**
     * @const MULTIPLE_CHOICE_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\Model'
     */
    const MULTIPLE_CHOICE_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\MultipleChoice\Model';

    /**
     * @const GROUP_ITEMS_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Model'
     */
    const GROUP_ITEMS_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\GroupItems\Model';

    /**
     * @const ORDER_ITEMS_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\Model'
     */
    const ORDER_ITEMS_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OrderItems\Model';

    /**
     * @const PAIR_ITEMS_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\Model'
     */
    const PAIR_ITEMS_MODEL_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\PairItems\Model';

    /**
     * @const OPEN_ENDED_QUESTION_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\Model'
     */
    const OPEN_ENDED_QUESTION_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\OpenEndedQuestion\Model';

    /**
     * @const TEXT_WITH_HOLES_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\TextWithHoles\Model'
     */
    const TEXT_WITH_HOLES_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\TextWithHoles\Model';

    /**
     * @var int $id Id of exercise model
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "exercise"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $id;

    /**
     * @var string $type
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "exercise"})
     * @Assert\NotBlank(groups={"create"})
     */
    protected $type;

    /**
     * @var string $title
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "exercise"})
     * @Assert\NotBlank(groups={"create"})
     */
    protected $title;

    /**
     * @var CommonModel $content
     * @Serializer\Groups({"details"})
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel")
     * @Assert\Valid
     */
    protected $content;

    /**
     * @var boolean $draft
     * @Serializer\Groups({"details", "list"})
     * @Serializer\Type("boolean")
     * @Assert\NotNull(groups={"create"})
     */
    protected $draft;

    /**
     * @var boolean $complete
     * @Serializer\Groups({"details", "list"})
     * @Serializer\Type("boolean")
     * @Assert\IsNull(groups={"create"})
     */
    protected $complete;

    /**
     * @var string $completeError
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "exercise"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $completeError;

    /**
     * @var boolean $removable
     * @Serializer\Groups({"details", "list"})
     * @Serializer\Type("boolean")
     * @Assert\IsNull(groups={"create"})
     */
    protected $removable;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $author;

    /**
     * @var array $requiredExerciseResources
     * @Serializer\Type("array")
     * @Serializer\Groups({"details"})
     * @Assert\IsNull()
     */
    private $requiredExerciseResources;

    /**
     * @var array $requiredKnowledges
     * @Serializer\Type("array")
     * @Serializer\Groups({"details"})
     * @Assert\IsNull()
     */
    private $requiredKnowledges;

    /**
     * @var int $owner
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $owner;

    /**
     * @var bool $public
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $public;

    /**
     * @var bool $archived
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $archived;

    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource>")
     * @Serializer\Groups({"details","list"})
     */
    protected $metadata;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"details","list"})
     */
    protected $keywords;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"details","list"})
     */
    protected $directories;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details"})
     */
    protected $parent;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details"})
     */
    protected $forkFrom;
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details"})
     */
    protected $hasAttempts;

    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource>")
     * @Serializer\Groups({"details"})
     */
    protected $exercises;
    /**

    /**
     * Set content
     *
     * @param \SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModel\Common\CommonModel
     */
    public function getContent()
    {
        return $this->content;
    }

    public function getDirectories()
    {
        return $this->directories;
    }

    public function setDirectories($dirs)
    {
        $this->directories = $dirs;
    }

    /**
     * Set requiredExerciseResources
     *
     * @param array $requiredExerciseResources
     */
    public function setRequiredExerciseResources($requiredExerciseResources)
    {
        $this->requiredExerciseResources = $requiredExerciseResources;
    }

    /**
     * Get requiredExerciseResources
     *
     * @return array
     */
    public function getRequiredExerciseResources()
    {
        return $this->requiredExerciseResources;
    }

    /**
     * Set requiredKnowledges
     *
     * @param array $requiredKnowledges
     */
    public function setRequiredKnowledges($requiredKnowledges)
    {
        $this->requiredKnowledges = $requiredKnowledges;
    }

    /**
     * Get requiredKnowledges
     *
     * @return array
     */
    public function getRequiredKnowledges()
    {
        return $this->requiredKnowledges;
    }
    public function addExercise(ExerciseResource $exercise)
    {
      $this->exercises[] = $exercise;
      return $this;
    }

    public function removeExercise(ExerciseResource $exercise)
    {
      $this->exercises->removeElement($exercise);
    }

    /**
     * Set exercises
     *
     * @param array $exercises
     */
    public function setExercises($exercises)
    {
        $this->exercises = $exercises;
    }

    /**
     * Get exercises
     *
     * @return array
     */
    public function getExercises()
    {
        return $this->exercises;
    }

    /**
     * Return the item serialization class corresponding to the type
     *
     * @param string $type
     *
     * @return string
     * @throws \LogicException
     */
    public function getSerializationClass($type)
    {
        switch ($type) {
            case CommonExercise::MULTIPLE_CHOICE:
                $class = self::MULTIPLE_CHOICE_MODEL_CLASS;
                break;
            case CommonExercise::GROUP_ITEMS:
                $class = self::GROUP_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::ORDER_ITEMS:
                $class = self::ORDER_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::PAIR_ITEMS:
                $class = self::PAIR_ITEMS_MODEL_CLASS;
                break;
            case CommonExercise::OPEN_ENDED_QUESTION:
                $class = self::OPEN_ENDED_QUESTION_CLASS;
                break;
            case CommonExercise::TEXT_WITH_HOLES:
                $class = self::TEXT_WITH_HOLES_CLASS;
                break;
            default:
                throw new \LogicException('Unknown type');
        }

        return $class;
    }

    /**
     * Return the item serialization class corresponding to the type of the object
     *
     * @return string
     * @throws \LogicException
     */
    public function getClass()
    {
        return self::getSerializationClass($this->type);
    }
    
    /**
     * Get hasAttempts.
     *
     * @return hasAttempts.
     */
    public function getHasAttempts()
    {
        return $this->hasAttempts;
    }
    
    /**
     * Set hasAttempts.
     *
     * @param hasAttempts the value to set.
     */
    public function setHasAttempts($hasAttempts)
    {
        $this->hasAttempts = $hasAttempts;
    }
}
