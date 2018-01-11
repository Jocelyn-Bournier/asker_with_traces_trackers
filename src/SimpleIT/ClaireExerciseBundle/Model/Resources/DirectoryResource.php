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
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResource as EM;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DirectoryResource as DR;

/**
 * Class DirectoryResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class DirectoryResource
{
    /**
     * @const RESOURCE_NAME = 'Directory'
     */
    const RESOURCE_NAME = 'Directory';

    /**
     * @var int $id Id of exercise
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "directory", "list"})
     */
    private $id;

    /**



    /**
     * @var string $name
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "directory"})
     */
    private $name;
    /**
     * @var string $code
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "directory"})
     */
    private $code;


    /**
     * @var int $totalSubs  totalSubs
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "directory", "list"})
     */
    private $totalSubs;
    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResource>")
     * @Serializer\Groups({"details"})
     */
    protected $models;
    /**
     * @var array
     * @Serializer\Type("array<SimpleIT\ClaireExerciseBundle\Model\Resources\DirectoryResource>")
     * @Serializer\Groups({"details"})
     */
    protected $subs;

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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Get models.
     *
     * @return models.
     */
    public function getModels()
    {
        return $this->models;
    }

    public function addModel(EM $model)
    {
      $this->models[] = $model;
      return $this;
    }

    public function removeModel(EM $model)
    {
      $this->models->removeElement($model);
    }

    
    /**
     * Get totalSubs.
     *
     * @return totalSubs.
     */
    public function getTotalSubs()
    {
        return $this->totalSubs;
    }
    
    /**
     * Set totalSubs.
     *
     * @param totalSubs the value to set.
     */
    public function setTotalSubs($totalSubs)
    {
        $this->totalSubs = $totalSubs;
    }
    
    /**
     * Get code.
     *
     * @return code.
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Set code.
     *
     * @param code the value to set.
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
    
    /**
     * Get subs.
     *
     * @return subs.
     */
    public function getSubs()
    {
        return $this->subs;
    }
    public function addSub(DR $directory)
    {
      $this->subs[] = $directory;
      return $this;
    }

    public function removeSub(DR $directory)
    {
      $this->subs->removeElement($directory);
    }
}
