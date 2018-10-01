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

abstract class AskerUserResourceFactory
{
    /**
     * Create an AskerUser Resource
     *
     */
    public static function create($user)
    {
        $audResource = new AskerUserResource();
        $audResource->setUsername($user->getUser()->getUsername());
        #$audResource->setId($user->getId());
#        $audResource->setIsManager($user->getIsManager());
        return $audResource;
    }
    public static function createFromArray($user)
    {
        $audResource = new AskerUserResource();
        $audResource->setUsername($user['username']);
        #$audResource->setId($user['id']);
#        $audResource->setIsManager($user->getIsManager());
        return $audResource;
    }
    public static function createCollectionFromArray($users)
    {
        $userResources = array();
        foreach ($users as $user) {
            $userResources[] = self::createFromArray($user);
        }

        return $userResources;
    }
}
