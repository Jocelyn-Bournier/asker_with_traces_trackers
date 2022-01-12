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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\Exercise\MultipleChoice;

use JMS\Serializer\Annotation as Serializer;

/**
 * An proposition contains the text of the proposition, if it is a good one and if it has
 * been ticked by the user (if propositioned).
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Proposition
{
    /**
     * @var string $text
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $text;

    /**
     * @var boolean $right
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "corrected", "not_corrected", "item_storage"})
     */
    private $right;

    /**
     * @var boolean $ticked
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"details", "corrected"})
     */
    private $ticked;

    /**
     * Constructor
     *
     * @param string  $text
     * @param boolean $right
     * @param boolean $ticked
     */
    function __construct($text, $right, $ticked)
    {
        $this->text = $text;
        $this->right = $right;
        $this->ticked = $ticked;
    }

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
     * Is right
     *
     * @return boolean
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set right
     *
     * @param boolean $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * Is ticked
     *
     * @return boolean
     */
    public function getTicked()
    {
        return $this->ticked;
    }

    /**
     * Set ticked
     *
     * @param boolean $ticked
     */
    public function setTicked($ticked)
    {
        $this->ticked = $ticked;
    }
}
