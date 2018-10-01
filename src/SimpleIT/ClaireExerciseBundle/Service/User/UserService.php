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

namespace SimpleIT\ClaireExerciseBundle\Service\User;

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Repository\AskerUserRepository;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;

/**
 * Service which manages the users
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class UserService extends TransactionalService implements UserServiceInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Set userRepository
     *
     * @param UserRepository $userRepository
     */
    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Find a user by its id
     *
     * @param int $userId
     *
     * @return AskerUser
     */
    public function get($userId)
    {
        return $this->userRepository->find($userId);
    }

    /**
     * Get all the users
     *
     * @return array
     */
    public function getAll()
    {
        $exerciseModel = null;

        return $this->userRepository->findAll();
    }

    /**
     * @param AskerUser   $user
     * @param string $roleName
     *
     * @return bool
     */
    public function hasRole($user, $roleName)
    {
        return array_search($roleName, $user->getRoles()) !== false;
    }

    public function allDisabled()
    {
        return $this->userRepository
            ->findBy(
                array('isEnable' => '0')
            )
        ;
    }

    public function allTeachers()
    {
        return $this->userRepository->findTeachers();
    }


}
