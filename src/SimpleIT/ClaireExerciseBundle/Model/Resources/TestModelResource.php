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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TestModelResource
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestModelResource
{
    /**
     * @const RESOURCE_NAME = 'Test Model'
     */
    const RESOURCE_NAME = 'Test Model';

    /**
     * @var integer $id Id of test
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "test_model", "test", "list"})
     * @Assert\Blank(groups={"edit", "create"})
     */
    private $id;

    /**
     * @var string $title
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "test_model", "test", "list"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $title;

    /**
     * @var integer $author Id of the author
     * @Serializer\Type("integer")
     * @Serializer\Groups({"details", "test_model", "test", "list"})
     * @Assert\Blank(groups={"edit", "create"})
     */
    private $author;

    /**
     * @var array $exerciseModels
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "test_model"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $exerciseModels;

    /**
     * Set exerciseModels
     *
     * @param array $exerciseModels
     */
    public function setExerciseModels($exerciseModels)
    {
        $this->exerciseModels = $exerciseModels;
    }

    /**
     * Get exerciseModels
     *
     * @return array
     */
    public function getExerciseModels()
    {
        return $this->exerciseModels;
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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * Set author
     *
     * @param int $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get author
     *
     * @return int
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
