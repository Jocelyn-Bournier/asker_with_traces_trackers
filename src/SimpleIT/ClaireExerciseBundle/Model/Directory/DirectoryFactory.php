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

namespace SimpleIT\ClaireExerciseBundle\Model\Directory;

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject;
//use SimpleIT\ClaireExerciseBundle\Repository\Exercise\CreatedExercise\StoredExerciseRepository;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DirectoryResource;
use SimpleIT\ClaireExerciseBundle\Serializer\Handler\AbstractClassForExerciseHandler;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AskerUserResourceFactory;


/**
 * Class ExerciseModelResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class DirectoryFactory
{


    /**
     * Create an Directory Resource
     *
     */
    public static function create(Directory $directory, $links = false)
    {
        $directoryResource = new DirectoryResource();
        $directoryResource->setName($directory->getName());
        $directoryResource->setIsVisible($directory->getIsVisible());
        $directoryResource->setOwner($directory->getOwner()->getId());
        $directoryResource->setId($directory->getId());
        if ($directory->getParent()){
            $directoryResource->setIsChild(true);
            $directoryResource->setCode($directory->getParent()->getCode());
        }else{
            $directoryResource->setIsChild(false);
            $directoryResource->setCode($directory->getCode());
        }
        foreach($directory->getUsers() as $user){
            if ($user->getUser()->getId() !== $directory->getOwner()->getId()){
                if ($user->getIsManager()){
                    $directoryResource->addManager(AskerUserResourceFactory::create($user));
                }
            }else{
                    $directoryResource->addManager(AskerUserResourceFactory::create($user));
            }
        }
        if (empty($directoryResource->getManagers())){
                $directoryResource->setManagers(array());
        }
        foreach($directory->getModels() as $model){
            //$directoryResource->addModel($model);
            $directoryResource->addModel(ExerciseModelResourceFactory::create($model,$links));
        }
        foreach($directory->getSubs() as $sub){
            $directoryResource->addSub(self::create($sub));
        }
        $directoryResource->setTotalSubs(count($directory->getSubs()));
        return $directoryResource;
    }
    public static function createProper(Directory $directory)
    {
        $directoryResource = new DirectoryResource();
        $directoryResource->setName($directory->getName());
        $directoryResource->setIsVisible($directory->getIsVisible());
        $directoryResource->setOwner($directory->getOwner()->getId());
        $directoryResource->setId($directory->getId());
        if ($directory->getParent()){
            $directoryResource->setIsChild(true);
            $directoryResource->setCode($directory->getParent()->getCode());
        }else{
            $directoryResource->setIsChild(false);
            $directoryResource->setCode($directory->getCode());
        }
        foreach($directory->getUsers() as $user){
            if ($user->getUser()->getId() !== $directory->getOwner()->getId()){
                if ($user->getIsManager()){
                    $directoryResource->addManager(AskerUserResourceFactory::create($user));
                }
            }else{
                    $directoryResource->addManager(AskerUserResourceFactory::create($user));
            }
        }
        if (empty($directoryResource->getManagers())){
                $directoryResource->setManagers(array());
        }
        foreach($directory->getModels() as $model){
            //$directoryResource->addModel($model);
            $directoryResource->addModel(ExerciseModelResourceFactory::createProper($model));
        }
        foreach($directory->getSubs() as $sub){
            $directoryResource->addSub(self::createProper($sub));
        }
        $directoryResource->setTotalSubs(count($directory->getSubs()));
        return $directoryResource;
    }
}
