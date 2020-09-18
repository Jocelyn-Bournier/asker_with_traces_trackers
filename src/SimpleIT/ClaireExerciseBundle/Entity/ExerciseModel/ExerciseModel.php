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

namespace SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\SharedEntity;

/**
 * Claire exercise model entity
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseModel extends SharedEntity
{
    /**
     * @var ExerciseModel
     */
    protected $parent;

    /**
     * @var ExerciseModel
     */
    protected $forkFrom;

    /**
     * @var Collection
     */
    private $exercises;

    /**
    /**
     * @var Collection
     */
    private $requiredExerciseResources;

    /**
     * @var Collection
     */
    private $requiredKnowledges;

    private $directories;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->requiredExerciseResources = new ArrayCollection();
        $this->requiredKnowledges = new ArrayCollection();
        $this->exercises = new ArrayCollection();
        $this->directories = new ArrayCollection();
    }

    /**
     * Set requiredExerciseResources
     *
     * @param \Doctrine\Common\Collections\Collection $requiredExerciseResources
     */
    public function setRequiredExerciseResources($requiredExerciseResources)
    {
        $this->requiredExerciseResources = $requiredExerciseResources;
    }

    /**
     * Get requiredExerciseResources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequiredExerciseResources()
    {
        return $this->requiredExerciseResources;
    }

    /**
     * Set requiredKnowledges
     *
     * @param \Doctrine\Common\Collections\Collection $requiredKnowledges
     */
    public function setRequiredKnowledges($requiredKnowledges)
    {
        $this->requiredKnowledges = $requiredKnowledges;
    }

    /**
     * Get requiredKnowledges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequiredKnowledges()
    {
        return $this->requiredKnowledges;
    }

    /**
     * Set parent
     *
     * @param \SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return \SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set forkFrom
     *
     * @param \SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel $forkFrom
     */
    public function setForkFrom($forkFrom)
    {
        $this->forkFrom = $forkFrom;
    }

    /**
     * Get forkFrom
     *
     * @return \SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel
     */
    public function getForkFrom()
    {
        return $this->forkFrom;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getTitle();
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->setTitle($name);
    }

    /**
     * Delete resource node
     */
    public function deleteResourceNode()
    {
        $this->resourceNode = null;
    }

    /**
     * Set exercises
     *
     * @param \Doctrine\Common\Collections\Collection $exercises
     */
    public function setExercises($exercises)
    {
        $this->exercises = $exercises;
    }

    /**
     * Get exercises
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExercises()
    {
        return $this->exercises;
    }
    
    /**
     * Get directories.
     *
     * @return directories.
     */
    public function getDirectories()
    {
        return $this->directories;
    }
    
    /**
     * Set directories.
     *
     * @param directories the value to set.
     */
    public function setDirectories($directories)
    {
        $this->directories = $directories;
    }


    public function __toString()
    {
        return "Je suis un modéle avec le nom ". $this->title;
    }
}
