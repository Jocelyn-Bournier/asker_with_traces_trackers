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
 * Class Sort
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Sort
{
    /**
     * const ASC = 'asc'
     */
    const ASC = 'asc';

    /**
     * @const DESC = 'desc';
     */
    const DESC = 'desc';

    /**
     * @var  string
     */
    private $property;

    /**
     * @var string asc | desc
     */
    private $order;

    /**
     * Constructor
     *
     * @param string $property Property
     * @param string $order    Order (asc | desc)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($property, $order = self::ASC)
    {
        $this->property = $property;
        $this->setOrder($order);
    }

    /**
     * Get order
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param string $order
     *
     * @throws \InvalidArgumentException
     */
    public function setOrder($order = self::ASC)
    {
        if (is_null($order)) {
            $order = self::ASC;
        }
        $order = strtolower($order);

        if (!in_array($order, array(self::ASC, self::DESC))) {
            throw new \InvalidArgumentException('Order must be asc or desc');
        }

        $this->order = $order;
    }

    /**
     * Get property
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set property
     *
     * @param string $property
     *
     * @codeCoverageIgnore
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

}
