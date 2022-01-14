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

namespace SimpleIT\ClaireExerciseBundle\Exception\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ApiException
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ApiException extends HttpException
{

    /*
     * CLIENT ERRORS
     */
    /**
     * BAD REQUEST = 400
     */
    const STATUS_CODE_BAD_REQUEST = 400;

    /**
     * UNAUTHORIZED = 401
     */
    const STATUS_CODE_UNAUTHORIZED = 401;

    /**
     * ACCESS DENIED = 403
     */
    const STATUS_CODE_ACCESS_DENIED = 403;

    /**
     *
     */
    const STATUS_CODE_NOT_FOUND = 404;

    /**
     * METHOD NOT ALLOWED
     */
    const STATUS_CODE_METHOD_NOT_ALLOWED = 405;

    /**
     * NOT ACCEPTABLE
     */
    const STATUS_CODE_NOT_ACCEPTABLE = 406;

    /**
     * CONFLICT
     */
    const STATUS_CODE_CONFLICT = 409;

    /**
     * REQUEST ENTITY TOO LARGE
     */
    const STATUS_CODE_REQUEST_ENTITY_TOO_LARGE = 413;

    /**
     * UNPROCESSABLE_ENTITY
     */
    const STATUS_CODE_UNPROCESSABLE_ENTITY = 422;

    /*
     * SERVER ERRORS
     */

    /**
     * INTERNAL SERVER ERROR = 500
     */
    const STATUS_CODE_INTERNAL_SERVER_ERROR = 500;

    /**
     * SERVICE UNVAILABLE = 503
     */
    const STATUS_CODE_SERVICE_UNVAILABLE = 503;

    // @codingStandardsIgnoreStart

    /**
     * @var array List of all the Client error codes
     */
    public static $statusCodesClientError = array(
        400,
        401,
        403,
        404,
        405,
        406,
        409,
        413,
        416,
        422
    );

    /**
     * @var array List of all the Server error codes
     */
    public static $statusCodesServerError = array(500, 503);

    // @codingStandardsIgnoreEnd

}
