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
use SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\CommonKnowledge;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class KnowledgeResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class KnowledgeResource extends SharedResource
{
    /**
     * @const RESOURCE_NAME = 'Knowledge'
     */
    const RESOURCE_NAME = 'Knowledge';

    /**
     * @const FORMULA_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula'
     */
    const FORMULA_CLASS = 'SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\Formula';

    /**
     * @var int $id Id of knowledge
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\Blank(groups={"create","edit"})
     */
    protected $id;

    /**
     * @var string $type
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\NotBlank(groups={"create"})
     * @Assert\Blank(groups={"edit"})
     */
    protected $type;

    /**
     * @var string $title
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\NotBlank(groups={"create"})
     */
    protected $title;

    /**
     * @var CommonKnowledge $content
     * @Serializer\Type("SimpleIT\ClaireExerciseBundle\Model\Resources\DomainKnowledge\CommonKnowledge")
     * @Serializer\Groups({"details"})
     * @Assert\Valid
     */
    protected $content;

    /**
     * @var boolean $draft
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $draft;

    /**
     * @var boolean $complete
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\IsNull(groups={"create"})
     */
    protected $complete;

    /**
     * @var string $completeError
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $completeError;

    /**
     * @var boolean $removable
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\IsNull(groups={"create"})
     */
    protected $removable;

    /**
     * @var array $requiredKnowledges
     * @Serializer\Type("array")
     * @Serializer\Groups({"details"})
     * @Assert\IsNull()
     */
    private $requiredKnowledges;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $author;

    /**
     * @var int $owner
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "list", "knowledge_list"})
     * @Assert\Blank(groups={"create", "edit"})
     */
    protected $owner;

    /**
     * @var bool $public
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list", "knowledge_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $public;

    /**
     * @var bool $archived
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details","list", "knowledge_list"})
     * @Assert\NotNull(groups={"create"})
     */
    protected $archived;

    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource>")
     * @Serializer\Groups({"details", "knowledge_list"})
     */
    protected $metadata;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "knowledge_list"})
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
     * @param CommonKnowledge $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return CommonKnowledge
     */
    public function getContent()
    {
        return $this->content;
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
    public function getSerializationClass($type)
    {
        switch ($type) {
            case CommonKnowledge::FORMULA:
                $class = self::FORMULA_CLASS;
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
