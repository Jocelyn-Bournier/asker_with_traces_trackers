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

namespace SimpleIT\ClaireExerciseBundle\Service\Directory;

use JMS\Serializer\SerializationContext;
use SimpleIT\ClaireExerciseBundle\Entity\AnswerFactory;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Exception\AnswerAlreadyExistsException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\CreatedExercise\AnswerRepository;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseCreation\ExerciseService;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
#class DirectoryService extends TransactionalService implements AnswerServiceInterface
class DirectoryService extends TransactionalService
{
    /**
     * @var  ExerciseService
     */
    private $exerciseService;

    /**
     * @var ItemService
     */
    private $itemService;

    /**
     * @var AttemptServiceInterface
     */
    private $attemptService;

    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Set serializer
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }
    /**
     * @var DirectoryRepository
     */
    private $directoryRepository;
    /**
     * Set directoryRepository
     *
     * @param UserRepository $directoryRepository
     */
    public function setDirectoryRepository($directoryRepository)
    {
        $this->directoryRepository = $directoryRepository;
    }

    /**
     * Set exerciseService
     *
     * @param ExerciseService $exerciseService
     */
    public function setExerciseService($exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    /**
     * Set attemptService
     *
     * @param AttemptServiceInterface $attemptService
     */
    public function setAttemptService($attemptService)
    {
        $this->attemptService = $attemptService;
    }

    /**
     * Set answerRepository
     *
     * @param AnswerRepository $answerRepository
     */
    public function setAnswerRepository($answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    /**
     * Set itemService
     *
     * @param ItemService $itemService
     */
    public function setItemService($itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * Create an answer to an item
     *
     * @param int            $itemId
     * @param AnswerResource $answerResource
     * @param int            $attemptId
     * @param int            $userId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\AnswerAlreadyExistsException
     * @return ItemResource
     */
    public function add($itemId, AnswerResource $answerResource, $attemptId, $userId)
    {
        // Get the item and the attempt
        if (count($this->getAll($itemId, $attemptId)) > 0) {
            throw new AnswerAlreadyExistsException();
        }
        $attempt = $this->attemptService->get($attemptId, $userId);
        /** @var Item $item */
        $item = $this->itemService->getByAttempt($itemId, $attemptId);

        $this->exerciseService->validateAnswer($item, $answerResource);

        $context = SerializationContext::create();
        $context->setGroups(array("answer_storage", 'Default'));
        $content = $this->serializer->jmsSerialize(
            $answerResource,
            'json',
            $context
        );

        $answer = AnswerFactory::create($content, $item, $attempt);
        // Add the answer to the database

        $this->em->persist($answer);
        $this->em->flush();

        $itemResource = $this->itemService->findItemAndCorrectionByAttempt(
            $itemId,
            $attemptId,
            $userId
        );

        $answer->setMark($itemResource->getContent()->getMark());
        $this->em->flush();

        return $itemResource;

    }

    /**
     * Get all answers for an item
     *
     * @param int  $itemId Item id
     * @param int  $attemptId
     * @param null $userId
     *
     * @return array
     */
    public function getAll($itemId = null, $attemptId = null, $userId = null)
    {
        $item = null;
        $attempt = null;

        if (!is_null($itemId)) {
            if (!is_null($attemptId)) {
                $attempt = $this->attemptService->get($attemptId, $userId);
                $item = $this->itemService->getByAttempt($itemId, $attemptId);
            } else {
                $item = $this->itemService->get($itemId);
            }
        }

        return $this->answerRepository->findAllBy($item, $attempt);
    }
    public function all(){
        return $this->directoryRepository->findAll();
    }

    public function allParents()
    {
        return $this->directoryRepository->findParents();
    }

    public function find($id)
    {
        return $this->directoryRepository->find($id);
    }
    public function remove($id)
    {
        $entity = $this->directoryRepository->find($id);
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function edit($resource,$userId)
    {
        $find = 0;
        if (is_null($resource->getId())) {
            throw new MissingIdException();
        }
        $entity = $this->find($resource->getId());
        foreach($entity->getUsers() as $u){
            if($u->getId() == $userId){
                $find = 1;
            }
        }
        if (!$find){
            throw new AccessDeniedException();
        }
        $entity->setName($resource->getName()); 
        $entity->setCode($resource->getCode());
        foreach($entity->getModels() as $model){
            $entity->removeModel($model);
        }
        if (!is_null($resource->getModels())){
            $repo = $this->em
                ->getRepository('SimpleITClaireExerciseBundle:ExerciseModel\ExerciseModel')
            ;
            foreach($resource->getModels() as $model){
                $entity->addModel($repo->find($model->getId()));
            }
        }
        $this->em->flush();
        return $entity;
    }
    public function create($user, $directory)
    {
        $dir = new Directory();
        $dir->setName('Nouveau répertoire');
        if ($directory != 0){
            $dir->setParent($this
                ->directoryRepository
                ->find($directory)
            );
        }
        $dir->addUser($user);
        $this->em->persist($dir);
        $this->em->flush();
        return $dir;
    }

    public function stats(Directory $directory, $attempt, $answer,$users)
    {
        $models = array();
        foreach($directory->getModels() as $model){
            $models[$model->getId()]['title'] = $model->getTitle();
            $models[$model->getId()]['userAnswer'] = $answer->
                uniqueUsersByModel($model->getId())[0]['total']
            ;
            $models[$model->getId()]['userNoAnswer'] =  $attempt->
                uniqueUsersByModel($model->getId())[0]['total']
            ;
            $models[$model->getId()]['avgAttempt'] = $attempt->
                averageAttemptByModel($model->getId())[0]['avg']
            ;
            $models[$model->getId()]['avgAnswer'] = $answer->
                averageAnswerByModel($model->getId())[0]['avg']
            ;
            $models[$model->getId()]['avgMark'] = $answer->
                averageMarkByModel($model->getId())[0]['avg']
            ;
            $models[$model->getId()]['users'] =  $users;

        }
        return $models;
    }

    public function getModelStats(Directory $directory)
    {
        $models = array();
        $dirs = array();
        $attempt = $this->em
            ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Attempt')
        ;
        $answer = $this->em
            ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Answer')
        ;
        $users = count($directory->getUsers());
        $dirs[$directory->getName()]= $this->stats(
            $directory,
            #$models,
            $attempt,
            $answer,
            $users
        );
        foreach($directory->getSubs() as $sub){
            #$models = $this->stats(
            $dirs[$sub->getName()] = $this->stats(
                $sub,
                #$models,
                $attempt,
                $answer,
                $users
            );
        }
        return $dirs;
    }
}
