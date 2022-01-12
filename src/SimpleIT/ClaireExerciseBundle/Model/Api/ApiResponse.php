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

use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiResponse
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ApiResponse extends Response
{
    /* SUCCESSFUL */
    /**
     *  OK = 200
     */
    const STATUS_CODE_OK = 200;

    /**
     * CREATED = 201
     */
    const STATUS_CODE_CREATED = 201;

    /**
     * NO CONTENT = 204
     */
    const STATUS_CODE_NO_CONTENT = 204;

    /**
     * PARTIAL CONTENT = 206
     */
    const STATUS_CODE_PARTIAL_CONTENT = 206;

    const STATUS_CODE_CONTENT_NOT_FOUND = 404;

    /**
     * @var array
     */
    public static $statusCodeSuccessful = array(200, 201, 202, 204, 206);

    /**
     * @var mixed The returned resource
     */
    private $resource;

    /**
     * @var array
     */
    private $groups;

    /**
     * Constructor
     *
     * @param mixed   $resource   Resource
     * @param integer $statusCode Http status code
     * @param array   $groups     Groups for allowed fields
     * @param array   $headers    Headers of the response
     */
    public function __construct(
        $resource,
        $statusCode = self::STATUS_CODE_OK,
        array $groups = array(),
        array $headers = array()
    )
    {
        $this->resource = $resource;
        $this->groups = $groups;
        parent::__construct('', $statusCode, $headers);
    }

    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
