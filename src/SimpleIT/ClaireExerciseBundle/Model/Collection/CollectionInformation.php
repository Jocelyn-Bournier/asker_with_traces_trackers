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

namespace SimpleIT\ClaireExerciseBundle\Model\Collection;

/**
 * Class CollectionInformation
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class CollectionInformation
{
    /**
     * @var array
     */
    private $sorts;

    /**
     * @type array
     */
    private $defaultSort;

    /**
     * @type array
     */
    private $filters;

    /**
     * Constructor
     *
     * @param array $sorts   Sorts
     * @param array $filters Filters
     */
    public function __construct(
        array $sorts = array(),
        array $filters = array()
    )
    {
        $this->sorts = $sorts;
        $this->filters = $filters;
    }

    /**
     * Get sorts
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getSorts()
    {
        return $this->sorts;
    }

    /**
     * Set sorts
     *
     * @param array $sorts Sorts
     *
     * @codeCoverageIgnore
     */
    public function setSorts(array $sorts)
    {
        $this->sorts = $sorts;
    }

    /**
     * Add sort
     *
     * @param Sort $sort
     */
    public function addSort(Sort $sort)
    {
        $this->sorts[] = $sort;
    }

    /**
     * Remove sort
     *
     * @param Sort $sort Sort
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeSort(Sort $sort)
    {
        $key = array_search($sort, $this->sorts, true);

        if ($key !== false) {
            unset($this->sorts[$key]);

            return true;
        }

        return false;
    }

    /**
     * Get defaultSort
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getDefaultSort()
    {
        return $this->defaultSort;
    }

    /**
     * Set defaultSort
     *
     * @param array $defaultSort
     *
     * @codeCoverageIgnore
     */
    public function setDefaultSort(array $defaultSort)
    {
        $this->defaultSort = $defaultSort;
    }

    /**
     * Get filters
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set filters
     *
     * @param array $filters Filters
     *
     * @codeCoverageIgnore
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get filter
     *
     * @param string $filter Filter
     *
     * @return string
     */
    public function getFilter($filter)
    {
        $value = null;
        if (isset($this->filters[$filter])) {
            $value = $this->filters[$filter];
        }

        return $value;
    }

    /**
     * Add filter
     *
     * @param string $filter Filter
     * @param mixed  $value  Value
     */
    public function addFilter($filter, $value)
    {
        $this->filters[$filter] = $value;
    }

    /**
     * Remove filter
     *
     * @param string $filter Filter
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFilter($filter)
    {
        if (isset($this->filters[$filter])) {
            unset($this->filters[$filter]);

            return true;
        }

        return false;
    }
}
