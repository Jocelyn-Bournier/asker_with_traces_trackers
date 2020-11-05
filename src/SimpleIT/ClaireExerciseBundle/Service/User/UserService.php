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

    /**
     * Create local user in database
     * @param string $first
     * @param string $last
     * @param string $username
     * @param string $password
     * @param bool $enable
     *
     * @return AskerUser
     */
    public function createLocalUser($first, $last, $username, $password, $enable)
    {
        $user = new AskerUser();
        $user->setFirstName($first);
        $user->setLastName($last);
        echo "createing $username<br>";
        $user->setUsername($username);
        $user->setPassword(
            password_hash($password, PASSWORD_DEFAULT)
        );
        $user->setLdapEmployeeId(0);
        $user->setIsLdap(0);
        $user->setIsEnable($enable);
        $user->setLdapDn('');
        $user->setSalt(uniqid());
        try{
            $this->em->persist($user);
            $this->em->flush($user);
        }catch(\Doctrine\DBAL\DBALException $e){
            die("Symfony failed to create $username:". $e->getMessage());
        }
        return $user;

    }



    public function allTeachers()
    {
        return $this->userRepository->findTeachers();
    }


}
