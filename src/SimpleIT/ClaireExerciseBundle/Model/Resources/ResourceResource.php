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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ResourceResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ResourceResource extends SharedResource
{
    /**
     * @const METADATA_IS_RESOURCE_PREFIX = '__'
     */
    const METADATA_IS_RESOURCE_PREFIX = '__';

    /**
     * @const RESOURCE_NAME = 'Exercise Resource'
     */
    const RESOURCE_NAME = 'Exercise Resource';

    /**
     * @const MULTIPLE_CHOICE_QUESTION = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\MultipleChoiceQuestionResource'
     */
    const MULTIPLE_CHOICE_QUESTION_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\MultipleChoiceQuestionResource';

    /**
     * @const PICTURE = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\PictureResource'
     */
    const PICTURE_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\PictureResource';

    /**
     * @const SEQUENCE = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\SequenceResource'
     */
    const SEQUENCE_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\SequenceResource';

    /**
     * @const OPEN_ENDED_QUESTION = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OpenEndedQuestionResource'
     */
    const OPEN_ENDED_QUESTION_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OpenEndedQuestionResource';

    /**
     * @const TEXT = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextResource'
     */
    const TEXT_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextResource';

    /**
     * @var int $id Id of resource
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\Blank(groups={"create","edit"})
     */
    protected $id;

    /**
     * @var string $type
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\NotBlank(groups={"create"})
     * @Assert\Blank(groups={"edit"})
     */
    protected $type;

    /**
     * @var string $title
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\NotBlank(groups={"create"})
     */
    protected $title;

    /**
     * @var CommonResource $content
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource")
     * @Serializer\Groups({"details"})
     * @Assert\Valid
     */
    protected $content;

    /**
     * @var boolean $draft
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $draft;

    /**
     * @var boolean $complete
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\Null(groups={"create"})
     */
    protected $complete;

    /**
     * @var string $completeError
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $completeError;

    /**
     * @var boolean $removable
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\Null(groups={"create"})
     */
    protected $removable;

    /**
     * @var array $requiredExerciseResources
     * @Serializer\Type("array")
     * @Serializer\Groups({"details"})
     * @Assert\Null()
     */
    private $requiredExerciseResources;

    /**
     * @var array $requiredKnowledges
     * @Serializer\Type("array")
     * @Serializer\Groups({"details"})
     * @Assert\Null()
     */
    private $requiredKnowledges;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $author;

    /**
     * @var int $owner
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "resource_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $owner;

    /**
     * @var bool $public
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list", "resource_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $public;

    /**
     * @var bool $archived
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list", "resource_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $archived;

    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource>")
     * @Serializer\Groups({"details", "resource_list"})
     */
    protected $metadata;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "resource_list"})
     */
    protected $keywords;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details"})
     */
    protected $parent;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "resource_list"})
     */
    protected $forkFrom;

    /**
     * Set content
     *
     * @param CommonResource $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return \SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource
     */
    public function getContent()
    {
        return $this->content;
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

    /**
     * Return the item serialization class corresponding to the type
     *
     * @param string $type
     *
     * @return string
     * @throws \LogicException
     */
    static public function getSerializationClass($type)
    {
        switch ($type) {
            case CommonResource::MULTIPLE_CHOICE_QUESTION:
                $class = self::MULTIPLE_CHOICE_QUESTION_CLASS;
                break;
            case CommonResource::PICTURE:
                $class = self::PICTURE_CLASS;
                break;
            case CommonResource::SEQUENCE:
                $class = self::SEQUENCE_CLASS;
                break;
            case CommonResource::TEXT:
                $class = self::TEXT_CLASS;
                break;
            case CommonResource::OPEN_ENDED_QUESTION:
                $class = self::OPEN_ENDED_QUESTION_CLASS;
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
}
