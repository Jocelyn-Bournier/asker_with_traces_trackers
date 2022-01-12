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

namespace SimpleIT\ClaireExerciseBundle\Model\Api;

/**
 * Class ApiCreatedResponse
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ApiCreatedResponse extends ApiResponse
{
    /**
     * Constructor
     *
     * @param mixed $resource Created resource
     * @param array $groups   Serialization group
     *
     * @codeCoverageIgnore
     */
    public function __construct($resource, $groups = array())
    {
        parent::__construct($resource, ApiResponse::STATUS_CODE_CREATED, $groups);
    }
}
