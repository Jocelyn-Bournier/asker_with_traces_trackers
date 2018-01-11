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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject;

use JMS\Serializer\Annotation as Serializer;

/**
 * A metadata constraint is a unique constraint about a metadata designated by
 * its key. The constraint can be an equality, a list of values, a comparison
 * or an intervalle.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class MetadataConstraint
{
    /**
     * @const EXISTS = "exists"
     */
    const EXISTS = "exists";

    /**
     * @const IN = "in"
     */
    const IN = "in";

    /**
     * @const GREATER_THAN = "gt"
     */
    const GREATER_THAN = "gt";

    /**
     * @const GREATER_THAN_OR_EQUALS = "gte"
     */
    const GREATER_THAN_OR_EQUALS = "gte";

    /**
     * @const LOWER_THAN = "lt"
     */
    const LOWER_THAN = "lt";

    /**
     * @const LOWER_THAN_OR_EQUALS = "lte"
     */
    const LOWER_THAN_OR_EQUALS = "lte";

    /**
     * @const BETWEEN = "between"
     */
    const BETWEEN = "between";

    /**
     * @var string $key The metadata key
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $key = null;

    /**
     * @var array $values An array of strings. In the case of in, the array is really used as one
     * . In the case of between, only the 2 first positions (0 and 1) are used. In the case of
     * gt, gte, lt and lte, only the first position is used. In the case of exists,
     * the array is not used.
     * @Serializer\Type("array")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $values = array();

    /**
     * The comparator to be used for the constraint.
     * Possible values : exists, in, gt, gte, lt, lte, between.
     *
     * @var string $comparator.
     * @Serializer\Type("string")
     * @Serializer\Groups({"details", "exercise_model_storage"})
     */
    private $comparator = null;

    /**
     * get values
     *
     * @return array An array of string.
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * get comparator
     *
     * @return string The comparator
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * Get key
     *
     * @return string The metadata key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Set the comparison in "in" type and set the list of values.
     *
     * @param array $val The possible values
     */
    public function setValueIn(array $val)
    {
        $this->comparator = 'in';
        $this->values = $val;
    }

    /**
     * Set the comparison in "in" type and set the unique possible value.
     *
     * @param string $val The value
     */
    public function setEquals($val)
    {
        $this->comparator = 'in';
        $this->values = array(0 => $val);
    }

    /**
     * Add a value when the comparison is in "in" type. If it is not, same
     * function than with setEquals($val)
     *
     * @param string $val The value to be added to the list
     */
    public function addValue($val)
    {
        if ($this->comparator == 'in') {
            $this->values[] = $val;
        } else {
            $this->setEquals($val);
        }
    }

    /**
     * Set the comparison in the specified type : lt, lte, gt or gte. Set the
     * value to compare with.
     *
     * @param string $comp
     * @param string $val
     */
    public function setComparison($comp, $val)
    {
        $this->comparator = $comp;
        $this->values = array(0 => $val);
    }

    /**
     * Set the comparison in "between" type. Set the min and max values.
     *
     * @param string $valMin A string reprentation of the min bound
     * @param string $valMax A string reprentation of the max bound
     */
    public function setBetween($valMin, $valMax)
    {
        $this->comparator = 'between';
        $this->values = array(0 => $valMin, 1 => $valMax);
    }

    /**
     * Set the comparison type to "exists", which means this metadata key exists
     * but which imposes no constraint about the value.
     */
    public function setExists()
    {
        $this->comparator = 'exists';
        $this->values = array();
    }

    /**
     * Build the metadataConstraint in a hard way, specifying all the parameters
     *
     * @param string $key         The key of the metadata
     * @param array  $values      An array of strings which are the values
     * @param string $comparator  The comparator: exists, in, lt, lte, gt, gte or between.
     */
    function build($key, $values, $comparator)
    {
        $this->key = $key;
        $this->values = $values;
        $this->comparator = $comparator;
    }

    /**
     * Set values
     *
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }
}
