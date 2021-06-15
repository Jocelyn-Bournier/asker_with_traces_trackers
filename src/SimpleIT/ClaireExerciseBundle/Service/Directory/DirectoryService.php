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
use SimpleIT\ClaireExerciseBundle\Entity\StatView;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Exception\AnswerAlreadyExistsException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AnswerResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ItemResource;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\CreatedExercise\AnswerRepository;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\ExerciseCreation\ExerciseService;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
#class DirectoryService extends TransactionalService implements AnswerServiceInterface
class DirectoryService extends TransactionalService
{
    /**
     * @var askerUserDirectoryService
     */
    private $askerUserDirectoryService;
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
     * Set askerUserDirectoryService
     *
     * @param UserRepository $askerUserDirectoryService
     */
    public function setaskerUserDirectoryService($askerUserDirectoryService)
    {
        $this->askerUserDirectoryService = $askerUserDirectoryService;
    }


    public function all(){
        return $this->directoryRepository->findAll();
    }

    public function countCurrentStudents($dir,$teachers)
    {
        return $this->directoryRepository->countCurrentStudents($dir, $teachers);
    }

    public function countOldStudents($dir,$teachers)
    {
        return $this->directoryRepository->countOldStudents($dir, $teachers);
    }

    public function allParents($user = 0)
    {
        return $this->directoryRepository->findParents($user);
    }
    public function findMine(AskerUser $user)
    {
        return $this->directoryRepository->findMine($user->getId());
    }

    public function findAllModelsIds($id)
    {
        return $this->directoryRepository->findAllModelsIds($id);
    }

    public function find($id)
    {
        return $this->directoryRepository->find($id);
    }
    public function findOneByName($name)
    {
        return $this->directoryRepository->findOneByName($name);
    }
    public function remove(Directory $id, AskerUser $user)
    {
        if (
            $id->getOwner()->getId() == $user->getId()
            || $user->isAdmin()
        ){
            try{
                $entity = $this->directoryRepository->find($id);
                foreach($entity->getModels() as $mod){
                    $entity->removeModel($mod);
                }
                $this->em->remove($entity);
                $this->em->flush();
            }catch (ForeignKeyConstraintViolationException $e){
               $res= new Response('Il est nécessaire de supprimer les sous-dossiers:'. $e->getMessage(), 500);
               $res->send();
            }
        }else{
            throw new AccessDeniedException();
        }
    }

    public function edit($resource,AskerUser $user)
    {
        $userId = $user->getId();
        $find = 0;
        if (is_null($resource->getId())) {
            throw new MissingIdException();
        }
        $entity = $this->find($resource->getId());
        if ($entity->getOwner()->getId() !== $userId
            && !$entity->hasManager($user)
        ){
            throw new AccessDeniedException();
        }
        $entity->setIsVisible($resource->getIsVisible());
        $entity->setName($resource->getName());
        if (!$entity->getParent()){
            $entity->setCode($resource->getCode());
            $entity->setFrameworkId($resource->getFrameworkId());
            $this->askerUserDirectoryService->updateManager($entity,$resource);
            foreach($entity->getSubs() as $dir){
                $this->askerUserDirectoryService->updateManager($dir, $resource);
            }
        }
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
        $dir->setIsVisible(true);
        $dir->setOwner($user);
        if ($directory != 0){
            $dir->setParent($this
                ->directoryRepository
                ->find($directory)
            );
        }
        $dirUser = new AskerUserDirectory();
        $dirUser->setUser($user);
        $dirUser->setIsManager(false);
        $dirUser->setDirectory($dir);
        $this->em->persist($dirUser);
        $this->em->persist($dir);
        $this->em->flush();
        return $dir;
    }

    public function JSONstats($repo,Directory $directory, $model, $view, $ids)
    {
        $datas = $repo->
            distributionMarkByModel($model->getId(),$view, $ids)[0]
        ;
        $json = array();
        foreach($datas as $key=> $val){
            $json[] = array('range' => $key, 'nb' => $val);
        }
        return $json;
    }
    public function JSONmodelMark($directory,$user)
    {
        return $this->directoryRepository->
            JSONmodelMark($directory,$user);
    }
    public function getModelStats(Directory $directory, $view, $ids)
    {
        $models = array();
        $dirs = array();
        $attempt = $this->em
            ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Attempt')
        ;
        $answer = $this->em
            ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Answer')
        ;
        #C'est horrible mais ca permet d'avoir l'id et le directoryName
        $dirs[$directory->getId()]['name'] = $directory->getName();
        $dirs[$directory->getId()]['models'] = $this->stats(
        #$dirs[$directory->getId().'+#='.$directory->getName()]= $this->stats(
            $directory,
            $attempt,
            $answer,
            $view,
            $ids
        );
        foreach($directory->getSubs() as $sub){
            $dirs[$sub->getId()]['name'] = $sub->getName();
            $dirs[$sub->getId()]['models'] = $this->stats(
            #$dirs[$sub->getId().'+#='. $sub->getName()] = $this->stats(
                $sub,
                $attempt,
                $answer,
                $view,
                $ids
            );
        }
        return $dirs;
    }
    public function stats(Directory $directory, $attempt, $answer,$view, $ids)
    {
        $models = array();
        foreach($directory->getModels() as $model){
            $models[$model->getId()]['title'] = $model->getTitle();
            $models[$model->getId()]['userAnswer'] = $answer->
                uniqueUsersByModel($model->getId(),$view, $ids)[0]['total']
            ;
            $models[$model->getId()]['userNoAnswer'] =  $attempt->
                uniqueUsersByModel($model->getId(),$view, $ids)[0]['total']
            ;
            $models[$model->getId()]['avgAttempt'] = $attempt->
                averageAttemptByModel($model->getId(),$view,$ids)[0]['avg']
            ;
            $models[$model->getId()]['avgAnswer'] = $answer->
                averageAnswerByModel($model->getId(),$view, $ids)[0]['avg']
            ;
            $models[$model->getId()]['avgMark'] = $answer->
                averageMarkByModel($model->getId(),$view, $ids)[0]['avg']
            ;
            $models[$model->getId()]['directoryId'] =  $directory->getId();
            $models[$model->getId()]['json'] = $this->JSONstats($answer,$directory,$model,$view,$ids);
        }
        return $models;
    }
    public function getIdUsers(Directory $directory, $view)
    {
        $this->getEntityManager();
        $ids = array();
        $users =$directory->realUsers();
        #return  array_column($this->em->getRepository('SimpleITClaireExerciseBundle:AskerUser')
        #->getArrayStudents($directory->getId(),$view->getStartDate(),$view->getEndDate()),'id');
        foreach($users as $user){
            if ($user->isOnlyStudent()){
                if($view){
                    $old = new \DateTime("2999-01-01");
                    foreach($user->getDirectories() as $aud){
                        if ($aud->getDirectory()->getId()  == $directory->getId()){
                            $old = $aud->getEndDate();
                            break;
                        }
                    }
                    $old = new \DateTime("2999-01-01");
                    foreach($user->getLogs() as $log){
                        if ($log->getLoggedAt() >= $view->getStartDate()
                            && $log->getLoggedAt() <= $view->getEndDate()
                            && $old >= $view->getEndDate()
                        ){
                            $ids[] = $user->getId();
                            break;
                        }
                    }
                }else{
                    $ids[] = $user->getId();
                }
            }
        }
        return $ids;


    }
    public function getUsernames(Directory $directory, $view)
    {
        $ids = array();
        $users = $directory->realUsers();
        foreach($users as $user){
            if ($user->isOnlyStudent()){
                if($view){
                    $old = new \DateTime("2999-01-01");
                    foreach($user->getDirectories() as $aud){
                        if ($aud->getDirectory()->getId()  == $directory->getId()){
                            $old = $aud->getEndDate();
                            break;
                        }
                    }
                    $old = new \DateTime("2999-01-01");
                    foreach($user->getLogs() as $log){
                        if ($log->getLoggedAt() >= $view->getStartDate()
                            && $log->getLoggedAt() <= $view->getEndDate()
                            && $old >= $view->getEndDate()
                        ){
                            $ids[$user->getId()] = $user->getUsername();
                            break;
                        }
                    }
                }else{
                    $ids[$user->getId()] = $user->getUsername();
                }
            }
        }
        return $ids;


    }
    public function getUsers(Directory $directory, $view)
    {
        $this->getEntityManager();
        $finalUsers = array();
        $users =$directory->realUsers();
        #return  array_column($this->em->getRepository('SimpleITClaireExerciseBundle:AskerUser')
        #->getArrayStudents($directory->getId(),$view->getStartDate(),$view->getEndDate()),'id');
        foreach($users as $user){
            if ($user->isOnlyStudent()){
                if($view){
                    $old = new \DateTime("2999-01-01");
                    foreach($user->getDirectories() as $aud){
                        if ($aud->getDirectory()->getId()  == $directory->getId()){
                            $old = $aud->getEndDate();
                            break;
                        }
                    }
                    $old = new \DateTime("2999-01-01");
                    foreach($user->getLogs() as $log){
                        if ($log->getLoggedAt() >= $view->getStartDate()
                            && $log->getLoggedAt() <= $view->getEndDate()
                            && $old >= $view->getEndDate()
                        ){
                            $finalUsers[] = $user;
                            break;
                        }
                    }
                }else{
                    $finalUsers[] = $user;
                }
            }
        }
        return $finalUsers;

    }


    public function getColumnStats(Directory $directory, $model, $view, $ids)
    {
        $models = array();
        $dirs = array();
        $answer = $this->em
            ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Answer')
        ;
        $dirs[$directory->getName()]= $this->JSONstats(
            $directory,
            $model,
            $answer,
            $view,
            $ids
        );
        foreach($directory->getSubs() as $sub){
            $dirs[$sub->getName()] = $this->JSONstats(
                $sub,
                $model,
                $answer,
                $view,
                $ids
            );
        }
        return $dirs;
    }


    public function hasView(Directory $directory)
    {
        $totalPeda= $this->em
            ->getRepository('SimpleITClaireExerciseBundle:Pedagogic')
            ->periodByCode($directory->getCode())
        ;
        $totalViews = array();
        foreach($directory->getStatViews() as $view){
            $totalViews[] =  $view->getRefPedagogic();
        }
        foreach($totalPeda as $peda){
            if (isset($peda['period'])){
                if (!in_array($peda['year']."-".$peda['period'], $totalViews)){
                    $view = new StatView();
                    $view->setRefPedagogic($peda['year']."-".$peda['period']);
                    $view->setDirectory($directory);
                    if(preg_match("/GP1AUTOM/", $peda['period'])){
                        $view->setName($peda['year']."/". explode('-',$peda['period'])[0]);
                        $view->setStartDate(new \DateTime($peda['year']."-08-15"));
                        $view->setEndDate(new \DateTime(($peda['year']+1)."-01-15"));
                    }else if (preg_match("/GP2PRINT/", $peda['period'])){
                        $view->setName(($peda['year']+1)."/". explode('-',$peda['period'])[0]);
                        $view->setStartDate(new \DateTime(($peda['year']+1)."-01-15"));
                        $view->setEndDate(new \DateTime(($peda['year']+1)."-08-15"));
                    }else{
                        $view->setName($peda['year']."/". explode('-',$peda['period'])[0]);
                        $view->setStartDate(new \DateTime($peda['year']."-08-15"));
                        $view->setEndDate(new \DateTime(($peda['year']+1)."-08-15"));
                    }
                    $this->em->persist($view);
                    try{
                        $this->em->flush();
                        $this->em->refresh($directory);
                    }catch(\Exception $e){
                        die('Une erreur est survenue!');
                    }
                }
            }
        }
    }

    public function getPreviewStats(Directory $directory, $users, $views)
    {
        $stats = array();
        foreach ($users as $key => $user) {
            $stat = $this->directoryRepository->
                getPreviewStats($directory->getId(),$user->getId(),$views)
            ;

            $stats[$key]['user'] = $user;
            $stats[$key]['count'] = $stat[0]['count'];
            if($stat[0]['count'] > 0){
                $stats[$key]['mark'] = round($stat[0]['mark'],2);
                $stats[$key]['firstDate'] = $stat[0]['firstDate'];
                $stats[$key]['lastDate'] = $stat[0]['lastDate'];
                $stats[$key]['firstDate2'] = $stat[0]['firstDate2'];
                $stats[$key]['lastDate2'] = $stat[0]['lastDate2'];
            }
            else{
                $stats[$key]['mark'] = "-";
                $stats[$key]['firstDate'] = "-";
                $stats[$key]['lastDate'] = "-";
                $stats[$key]['firstDate2'] = "-";
                $stats[$key]['lastDate2'] = "-";
            }
        }
        return $stats;
    }

    public function exportTomuss($model, $users, $view)
    {
        $answer = $this->em
            ->getRepository('SimpleITClaireExerciseBundle:CreatedExercise\Answer')
        ;
        return $answer->exportTomuss($model, $users, $view);
    }

    public function changeVisibility(AskerUser $user, Directory $directory)
    {
        if (
            $directory->getOwner()->getId() == $user->getId()
            || $user->isAdmin()
            || $directory->hasManager($user)
        ){
            if ($directory->getIsVisible()){
                $directory->setIsVisible(false);
            }else{
                $directory->setIsVisible(true);
            }
            $this->em->flush();
        }else{
            throw new AccessDeniedException();
        }

    }
}
