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
 * Class ApiHeader
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ApiHeader
{
    /**
     * @const CONTENT_RANGE = 'Content-Range'
     */
    const CONTENT_RANGE = 'Content-Range';

    /**
     * @const CONTENT_LENGTH = 'Content-Length'
     */
    const CONTENT_LENGTH = 'Content-Length';

    /**
     * @const CONTENT_TYPE = 'Content-Type';
     */
    const CONTENT_TYPE = 'Content-Type';

    /**
     * @const ALLOW = 'Allow'
     */
    const ALLOW = 'Allow';

    /**
     * @const FORMAT_DEFAULT = 'json'
     */
    const FORMAT_DEFAULT = self::FORMAT_JSON;

    /**
     * @const FORMAT_HTML = 'html'
     */
    const FORMAT_HTML = 'html';

    /**
     * @const FORMAT_JSON = 'json'
     */
    const FORMAT_JSON = 'json';

    /**
     * @const FORMAT_XML = 'xml'
     */
    const FORMAT_XML = 'xml';

}
