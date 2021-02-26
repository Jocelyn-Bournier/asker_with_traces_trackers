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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\Directory;

#TODEL
use Symfony\Component\HttpFoundation\Response;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiCreatedResponse;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiAccessDeniedException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiDeletedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiEditedResponse;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\DirectoryResource;
use SimpleIT\ClaireExerciseBundle\Model\Directory\DirectoryFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AttemptResourceFactory;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResourceFactory;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API Attempt controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class DirectoryController extends BaseController
{
    /**
     * Get a specific Attempt resource
     *
     * @param int $directoryId
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction(Directory $directoryId)
    {
        try {
            if (
                $this->getUser()->getId() == $directoryId->getOwner()->getId()
                || $directoryId->hasManager($this->getUser())
            ){
                $directoryResource = DirectoryFactory::create($directoryId);
                return new ApiGotResponse($directoryResource, array("details", 'Default'));
            }else{
                throw new AccessDeniedException();
            }

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AttemptResource::RESOURCE_NAME);
        }
    }
    public function viewExercisesAction(Directory $directoryId)
    {
        $allowed = 0;
        try {
            foreach($this->getUser()->realDirectories() as $dir){
                if ($dir->getId() == $directoryId->getId()){
                //if ($dir->getDirectory()->getId() == $directoryId->getId()){
                    $allowed = 1;
                }
            }
            if ($allowed){
                $directoryResource = DirectoryFactory::createProper($directoryId,true);
                if (!empty($directoryResource->getModels())){
                    foreach($directoryResource->getModels() as $model){
                        $model = $this->loadDirectory($this->getUser(), $model);
                    }
                }
                if (!empty($directoryResource->getSubs())){
                    foreach($directoryResource->getSubs() as $sub){
                        if (!empty($sub->getModels())){
                            foreach($sub->getModels() as $model){
                                $model = $this->loadDirectory($this->getUser(), $model);
                            }
                        }
                    }
                }
                return new ApiGotResponse($directoryResource, array("details", 'Default'));
            }else{
                throw new ApiAccessDeniedException('You cannot open this directory');
            }
        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AttemptResource::RESOURCE_NAME);
        }
    }
    /**
     * Get the list of directories short
     *
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiBadRequestException
     * @return ApiGotResponse
     */
    public function listAction(CollectionInformation $collectionInformation)
    {
        $user = $this->get('security.context')->getToken()->getUser()->getId();
        $directories = $this->getDoctrine()
            ->getRepository('SimpleITClaireExerciseBundle:Directory')
            ->findAllApi($user);

        $format= array();
        foreach($directories as $d){
            if(empty($d['parent_name'])){
                $name = $d['name'];
            }else{
                $name =$d['parent_name'].": ".$d['name'];

            } 
            $format[] = array('id' => $d['id'], 'name' => $name);

        }
        return new ApiGotResponse($format, array('list', 'Default'));
    }
    /**
     * Get the list of directories longest
     *
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiBadRequestException
     * @return ApiGotResponse
     */
    public function mineAction(CollectionInformation $collectionInformation)
    {
        $user = $this->get('security.context')->getToken()->getUser()->getId();
        $repo = $this->getDoctrine()
            ->getRepository('SimpleITClaireExerciseBundle:Directory')
        ;
        $directories = $repo
            ->findMine($user);
        foreach($directories as $key =>  $dir){
            if(!isset($dir['idp'])){
                $val = $repo->countChildrens($dir["id"]);
                $directories[$key]["subs"] = $val[0]["total"];
            }
            $directories[$key]["models"] = $repo->countModels($dir["id"])[0]["total"];
        }
        return new ApiGotResponse($directories, array('list', 'Default'));
    }

    /**
     * SAVE IT
     *
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiBadRequestException
     * @return ApiGotResponse
     */
    public function modelDirectoryAction(
        CollectionInformation $collectionInformation,
        Directory $directory,
        ExerciseModel $model)
    {
        $directory->addModel($model);
        $this->getDoctrine()->getEntityManager()->flush();

        return new ApiGotResponse($directory, array('list', 'Default'));
    }
    /**
     * Add Model in Directory
     *
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiBadRequestException
     * @return ApiGotResponse
     */
    public function modelAction(
        CollectionInformation $collectionInformation,
        $model)
    {
        $directories = $this->getDoctrine()
            ->getRepository('SimpleITClaireExerciseBundle:Directory')
            //->findAll();
            ->findByModel($model);
        //foreach($directories as $dir){
        //    //echo "dir " . $dir->getName();
        //    echo $dir["name"];
        //}

        return new ApiGotResponse($directories, array('list', 'Default'));
    }
    /**
     * Get the list of directories
     *
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiBadRequestException
     * @return ApiGotResponse
     */
    #dead code i guess 27/11/2019
    #public function newInDirectoryAction(
    #    CollectionInformation $collectionInformation,
    #    $user)
    #{
    #    $directories = $this->getDoctrine()
    #        ->getRepository('SimpleITClaireExerciseBundle:Directory')
    #        //->findAll();
    #        ->findNews($user);

    #    return new ApiGotResponse($directories, array('list', 'Default'));
    #}
    /**
     * Delete a directory
     *
     * @param int $directoryId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiDeletedResponse
     */
    public function deleteAction(Directory $directoryId)
    {
        try {
            $this->get('simple_it.exercise.directory')->remove(
                $directoryId,
                $this->getUser()
            );

            return new ApiDeletedResponse();

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        } catch (EntityDeletionException $ede) {
            throw new ApiBadRequestException($ede->getMessage());
        }
    }
    /**
     * Edit a model
     *
     * @param ExerciseModelResource $modelResource
     * @param int                   $exerciseModelId
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     * @return ApiEditedResponse
     */
    public function editAction(DirectoryResource $directoryResource,$directoryId)
    {
        try {
            $directory = $this->get('simple_it.exercise.directory')->edit
                (
                    $directoryResource,
                    $this->getUser()
                );
            $directoryResource = DirectoryFactory::create($directory, false, 0);

            return new ApiEditedResponse($directoryResource);

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(DirectoryResource::RESOURCE_NAME);
        } catch (DBALException $eoe) {
            throw new ApiConflictException($eoe->getMessage());
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        } catch (InvalidTypeException $ite) {
            throw new ApiBadRequestException($ite->getMessage());
        }
    }
    /**
     * Create a new directory (without metadata)
     *
     * @param DirectoryResource $directoryResource
     *
     * @throws ApiBadRequestException
     * @throws ApiNotFoundException
     * @return ApiResponse
     */
    public function createAction(
        $id
    //    DirectoryResource $directoryResource
    )
    {
        try {
            $user = $this->getUser();
            $directory = $this
                ->get('simple_it.exercise.directory')
                ->create($user, $id)
            ;

            $dirResource = DirectoryFactory::create($directory);

            return new ApiCreatedResponse($dirResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ExerciseModelResource::RESOURCE_NAME);
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        }
    }


    public function loadDirectory($user, $model){
        $attempts = $this->getDoctrine()
            ->getRepository('SimpleITClaireExerciseBundle:Directory')
            ->findAttempts($user->getId(),$model->getId())
        ;
        if (empty($attempts)){
            $model->setHasAttempts(0);
        }else{
            $model->setHasAttempts(1);
            $exercises = $this->getDoctrine()
                ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\StoredExercise')
                ->findByModelUser($model->getId(), $user->getId());
            foreach($exercises as $exercise){
                $model->addExercise(ExerciseResourceFactory::createId($exercise));
            }
            foreach($model->getExercises() as $exercise){
                $attempts = $this->getDoctrine()
                    ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Attempt')
                    ->findByExerciseUser($exercise->getId(), $user->getId());
                foreach($attempts as $attempt){
                    $exercise->addAttempt(AttemptResourceFactory::create($attempt, true));
                }
            }
        }
        return $model;
    }

    public function visibleAction(Directory $directory)
    {
        try {
            $user = $this->getUser();
            $this
                ->get('simple_it.exercise.directory')
                ->changeVisibility($user, $directory)
            ;

            $dirResource = DirectoryFactory::create($directory);

            return new ApiCreatedResponse($dirResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ExerciseModelResource::RESOURCE_NAME);
        } catch (NoAuthorException $nae) {
            throw new ApiBadRequestException($nae->getMessage());
        }
    }


    public function clearStudentAction(Directory $directory)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if ($directory->getOwner()->getId() ==  $user->getId()
        || $user->isAdmin()){
            foreach($directory->getUsers() as $aud){
                if ($aud->getUser()->isOnlyStudent()){
                    $aud->setEndDate(new \DateTime());
                }
            }
            $this->getDoctrine()->getEntityManager()->flush();
            return $this->redirectToRoute('admin_stats');

        }else{
            throw new ApiAccessDeniedException('Vous ne pouvez pas effectuer cette opération');
        }
    }

    /**
     * ANR COMPER
     * Creates a Token JWT to send data to the COMPER services (profile & recommendations so far)
     */
    public function jwtAction($frameworkId, $role)
    {
        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.context')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+30;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "fwid"     => intval($frameworkId),
            "username" => $user->getUsername(),
            "role"     => $role,
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/'
        ];
        $token = $jwtEncoder->getToken($payload);
        
        $response = new JsonResponse(array('token' => $token));
        return $response;
    }
}
