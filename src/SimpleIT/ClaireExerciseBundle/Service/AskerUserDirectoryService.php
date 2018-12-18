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

namespace SimpleIT\ClaireExerciseBundle\Service;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
/**
 * Service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AskerUserDirectoryService extends TransactionalService
{
    /**
     * @var AskerUserDirectoryRepository
     */
    private $askerUserDirectoryRepository;

    /**
     * @var AskerUserRepository
     */
    private $askerUserRepository;

    /**
     * Set askerUserDirectoryRepository
     *
     * @param AskerUserDirectoryRepository  $askerUserDirectoryRepository
     */
    public function setAskerUserDirectoryRepository($repository)
    {
        $this->askerUserDirectoryRepository = $repository;
    }

    /**
     * Set askerUserRepository
     *
     * @param AskerUserRepository  $askerUserRepository
     */
    public function setAskerUserRepository($repository)
    {
        $this->askerUserRepository = $repository;
    }

    public function updateManager(Directory $dir, $data)
    {
        //return users with roles ROLE_WS_CREATOR and wont return owner
        foreach($dir->getManagers() as $user){
            $aud = $this->askerUserDirectoryRepository->findByUserIdDir($user->getUser()->getId(), $dir);
            if ($aud !== null)
            {
                $this->em->remove($aud);
            }
        }
        $this->em->flush();
        foreach($data->getManagers() as $manager){
            // $manager is a model ressource not an entity managed by doctrine
            $aud = $this->askerUserDirectoryRepository->findByUserIdDir($manager->getId(), $dir);
            // the owner already exist so we wont create him
            if ($aud === null){
                $aud = new AskerUserDirectory();
                $aud->setIsManager(true);
                $aud->setDirectory($dir);
                #$aud->setIsOld(false);
                //if inject wrong data it wont work
		$user = $this->askerUserRepository->findOneByUsername($manager->getUsername());
                $aud->setUser($user);
		$user->addDirectory($aud);
                $dir->addUser($aud);
                $this->em->persist($aud);
            }
        }
    }

    public function deleteChildrens(AskerUser $user, $directories)
    {
        $userId = $user->getId();
        foreach($directories as $directory){
            foreach($directory->getSubs() as $sub){
                $sub->getUsers()->filter(
                    function($aud) use ($userId){
                        if($aud->getUser()->getId() == $userId){
                            $this->em->remove($aud);
                        }
                    })
                ;
            }
        }
    }
    public function updateForUser(AskerUser $user)
    {
        foreach($user->getDirectories() as $aud){
            if ($aud->getDirectory()->getOwner()->getId() !== $user->getId()){
                $isManager = $aud->getIsManager();
                $end = $aud->getEndDate();
                $start = $aud->getStartDate();
                foreach($aud->getDirectory()->getSubs() as $sub){
                    $audSub = $this
                        ->askerUserDirectoryRepository
                        ->findByUserIdDir($user->getId(), $sub)
                    ;
                    if ($audSub === null){
                        $audSub = new AskerUserDirectory();
                        $this->em->persist($audSub);
                    }
                    $audSub->setIsManager($isManager);
                    $audSub->setDirectory($sub);
                    $audSub->setEndDate($end);
                    $audSub->setStartDate($start);
                    $audSub->setUser($user);
                }
            }
        }

    }
}

