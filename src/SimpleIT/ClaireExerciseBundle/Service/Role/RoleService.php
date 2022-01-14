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

namespace SimpleIT\ClaireExerciseBundle\Service\Role;

use SimpleIT\ClaireExerciseBundle\Entity\Role;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Repository\RoleRepository;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;

/**
 * Service which manages the roles
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class RoleService extends TransactionalService
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * Set roleRepository
     *
     * @param RoleRepository $userRepository
     */
    public function setRoleRepository($roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Find a user by its id
     *
     * @param int $roleId
     *
     * @return Role
     */
    public function get($roleId)
    {
        return $this->roleRepository->find($roleId);
    }

    /**
     * Get all the role
     *
     * @return array
     */
    public function getAll()
    {
        $exerciseModel = null;

        return $this->roleRepository->findAll();
    }

    /**
     * @param string $role
     *
     * @return Role
     */
    public function getRoleByName($role)
    {
        return $this->roleRepository->findOneBy(
            array('name' =>  "$role")
        );
    }

    /**
     * @return Role
     */
    public function getRoleUser()
    {
        return $this->roleRepository->findOneBy(
            array('name' =>  'ROLE_USER')
        );
    }

    /**
     * @param string $role
     * @param AskerUser $user
     *
     * @return Role
     */
    public function addRoleToUser($role, AskerUser $user)
    {
        $role = $this->getRoleByName($role);
        if ($role){
            $user->addRole($role);
            $this->em->flush();
            return 1;
        }
        return 0;
    }

}
