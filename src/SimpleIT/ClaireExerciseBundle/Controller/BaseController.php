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

namespace SimpleIT\ClaireExerciseBundle\Controller;

use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/**
 * API Controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class BaseController extends Controller
{
    /**
     * Get the current user's id
     *
     * @throws InsufficientAuthenticationException
     * @return int
     */
    protected function getUserId()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->get('security.token_storage')->getToken()->getUser()->getId();
        } else {
            throw new InsufficientAuthenticationException();
        }
    }

    /**
     * Validate a resource
     *
     * @param mixed   $resource         Resource to validate
     * @param array   $validationGroups Validation groups
     * @param Boolean $traverse         Whether to traverse the value if it is traversable.
     * @param Boolean $deep             Whether to traverse nested traversable values recursively.
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     */
    protected function validateResource(
        $resource,
        $validationGroups = array(),
        $traverse = false,
        $deep = false
    )
    {
        /*$violations = $this->get('validator')->validate(
            $resource,
            $validationGroups,
            $traverse,
            $deep
        );
        */
        $violations = $this->get('validator')->validate($resource);

        $formatedErrors = array();
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $formatedErrors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new ApiBadRequestException($formatedErrors);
        }
    }
}
