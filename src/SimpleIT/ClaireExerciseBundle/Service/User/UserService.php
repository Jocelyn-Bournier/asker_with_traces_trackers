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
use SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory;
use SimpleIT\ClaireExerciseBundle\Entity\Role;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;
use SimpleIT\ClaireExerciseBundle\Exception\UsernameUnknownFormat;

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
     * @var LdapServer
     */
    private $ldapServer;

    /**
     * Set ldapServer
     *
     * @param LdapServer $ldapServer
     */
    public function setLdapServer($ldapServer)
    {
        $this->ldapServer = $ldapServer;
    }


    /**
     * @var RoleService
     */
    private $roleService;

    /**
     * Set roleService
     *
     * @param RoleService $roleService
     */
    public function setRoleService($roleService)
    {
        $this->roleService = $roleService;
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
        return $this->userRepository->findAll();
    }


    /**
     * Get all users as userView
     *
     * @return array
     */
    public function getNativeAll()
    {
        return $this->userRepository->nativeAll();
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

    public function userExists($username){
        return $this->userRepository->findOneByUsername($username);
    }

    public function createUser($username, $res, $roles, $directory){
        try{
            $datas = $this->validUserFormat(trim($username));
            if (!$this->userExists($datas['username'])){
                if($datas['state'] == "student"){
                    $check = $this->createLDAPUser($datas['username']);
                }else{
                    $check = $this->createLocalUser(
                        $datas['firstName'],
                        $datas['lastName'],
                        $datas['username'],
                        $datas['password'],
                        1
                    );
                }
                if($check){
                    $res['ok'] += 1;
                    foreach($roles as $role){
                        $check->addRole($role);
                    }
                    $this->roleService->addRoleToUser("ROLE_USER",$check);
                    if (!empty($directory)){
                        $aud = new AskerUserDirectory();
                        $aud->setDirectory($directory);
                        $aud->setUser($check);
                        $aud->setIsManager(0);
                        $aud->setIsReader(0);
                        $check->addDirectory($aud);
                        try{
                            $this->em->persist($aud);
                            $this->em->flush($aud);
                        }catch(\Doctrine\DBAL\DBALException $e){
                            die("Symfony failed to:". $e->getMessage());
                        }
                    }
                }
            }else{
                $res['already'] += 1;

            }
        }catch(UsernameUnknownFormat $e){
            $res['error'] += 1;
            $res['error_msg'] .= $e->getMessage()."<br>";
            return $res;
        }
        return $res;

    }
    public function validUserFormat($username){
        # care if we want to allow to create LDAP teacher prenom.nom
        $reg = array("/^(p|[0-9])[0-9]{7}$/","/ext_[a-zA-Z]{1,}\.[a-zA-Z]{1,};[^;]{6,}/");
        $datas = array();
        if (preg_match("/^(p|[0-9])[0-9]{7}$/", $username)){
            $datas['state'] = "student";
            $datas['username'] = substr_replace($username,'p',0,1);
            return $datas;
        }
        if (preg_match($reg[1], $username)){
            $semiSplit = explode(";", $username);
            $underSplit = explode("_",$semiSplit[0]);
            $nameSplit = explode(".",$underSplit[1]);
            $datas['state'] = "ext";
            $datas['firstName'] = $nameSplit[0];
            $datas['lastName'] = $nameSplit[1];
            $datas['username'] = $semiSplit[0];
            $datas['password'] = $semiSplit[1];
            return $datas;
        }
        throw new UsernameUnknownFormat(
            "\"$username\" ne respecte aucun des formats attendus:
            p1234567, 11234567 ou \"ext_prenom.nom;password\" (au moins 6 lettres)"
        );
    }

    public function createUserFromFile($path, $roles, Directory $directory = null){
        $handle = fopen($path, "r");
        $res = array("ok"=>0,"already"=>0,"error" =>0,"error_msg"=>"");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $res = $this->createUser($line,$res, $roles, $directory);
            }
            fclose($handle);
        }
        return $res;
    }


    public function createLDAPUser($username){
        $dn = $this->ldapServer->searchUserDn($username);
        if ($dn === false){
            die("error$username");
        }
        $user = new AskerUser();
        $attributes = $this->ldapServer->getUserAttributes($username, array('employeeID'));
        $firstName = $this->ldapServer->getUserAttributes($username, array('givenName'));
        $lastName = $this->ldapServer->getUserAttributes($username, array('sn'));
        $user->setPassword(uniqid());
        $user->setSalt(uniqid());
        $user->setUsername(strtolower($username));
        $user->setFirstName($firstName[0]['givenname'][0]);
        $user->setLastName($lastName[0]['sn'][0]);
        $user->setLdapDn($dn);
        $user->setLdapEmployeeId($attributes[0]['employeeid'][0]);
        $user->setIsLdap(1);
        $user->setIsEnable(1);
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
