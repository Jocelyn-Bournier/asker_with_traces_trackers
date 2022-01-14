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

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;

/**
 * Class UserResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class UserResourceFactory
{

    /**
     * Create a user resource collection
     *
     * @param array $users
     *
     * @return array
     */
    public static function createCollection(array $users)
    {
        $userResources = array();
        foreach ($users as $user) {
            $userResources[] = self::create($user);
        }

        return $userResources;
    }

    /**
     * Create a user resource
     *
     * @param AskerUser $user
     *
     * @return UserResource
     */
    public static function create(AskerUser $user)
    {
        $userResource = new UserResource();
        $userResource->setId($user->getId());
        $userResource->setFirstName($user->getFirstName());
        $userResource->setLastName($user->getLastName());
        $userResource->setUserName($user->getUsername());

        return $userResource;
    }
}
