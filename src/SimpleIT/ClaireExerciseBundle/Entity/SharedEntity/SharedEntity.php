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

namespace SimpleIT\ClaireExerciseBundle\Entity\SharedEntity;

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Claire exercise knowledge entity
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class SharedEntity extends AbstractResource
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var  AskerUser
     */
    protected $author;

    /**
     * @var AskerUser
     */
    protected $owner;

    /**
     * @var boolean $draft
     */
    protected $draft;

    /**
     * @var boolean $complete
     */
    protected $complete;

    /**
     * @var string $completeError
     */
    protected $completeError;

    /**
     * @var Collection
     */
    protected $children;

    /**
     * @var SharedEntity
     */
    protected $parent;

    /**
     * @var Collection
     */
    protected $forkedBy;

    /**
     * @var SharedEntity
     */
    protected $forkFrom;

    /**
     * @var bool
     */
    protected $public;

    /**
     * @var boolean $archived
     */
    protected $archived;

    /**
     * @var Collection
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $keyword;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->metadata = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->forkedBy = new ArrayCollection();
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Set author
     *
     * @param AskerUser $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return AskerUser
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set archived
     *
     * @param boolean $archived
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
    }

    /**
     * Get archived
     *
     * @return boolean
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set children
     *
     * @param \Doctrine\Common\Collections\Collection $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set forkedBy
     *
     * @param \Doctrine\Common\Collections\Collection $forkedBy
     */
    public function setForkedBy($forkedBy)
    {
        $this->forkedBy = $forkedBy;
    }

    /**
     * Get forkedBy
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getForkedBy()
    {
        return $this->forkedBy;
    }

    /**
     * Set metadata
     *
     * @param \Doctrine\Common\Collections\Collection $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Get metadata
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set keyword
     *
     * @param array $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Get keyword
     *
     * @return array
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set owner
     *
     * @param SimpleIT\ClaireExerciseBundle\Entity\AskerUser $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner
     *
     * @return SimpleIT\ClaireExerciseBundle\Entity\AskerUser
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set forkFrom
     *
     * @param SharedEntity $forkFrom
     */
    public function setForkFrom($forkFrom)
    {
        $this->forkFrom = $forkFrom;
    }

    /**
     * Get forkFrom
     *
     * @return SharedEntity
     */
    public function getForkFrom()
    {
        return $this->forkFrom;
    }

    /**
     * Set parent
     *
     * @param SharedEntity $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return SharedEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set public
     *
     * @param boolean $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set complete
     *
     * @param boolean $complete
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;
    }

    /**
     * Get complete
     *
     * @return boolean
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set draft
     *
     * @param boolean $draft
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;
    }

    /**
     * Get draft
     *
     * @return boolean
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * Set completeError
     *
     * @param string $completeError
     */
    public function setCompleteError($completeError)
    {
        $this->completeError = $completeError;
    }

    /**
     * Get completeError
     *
     * @return string
     */
    public function getCompleteError()
    {
        return $this->completeError;
    }
}
