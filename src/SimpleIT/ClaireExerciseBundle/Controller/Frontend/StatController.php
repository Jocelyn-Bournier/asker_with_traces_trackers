<?php
/*
  This file is part of CLAIRE.
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

namespace SimpleIT\ClaireExerciseBundle\Controller\Frontend;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use SimpleIT\ClaireExerciseBundle\Entity\StatView;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiDeletedResponse;
use SimpleIT\ClaireExerciseBundle\Form\StatViewType;
use Symfony\Component\Security\Core\SecurityContext;
/**
 * Class StatController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class StatController extends BaseController
{
    public function statAction()
    {   
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')){
            $dirs = $this->get('simple_it.exercise.directory')->allParents();
        }else if ($this->get('security.context')->isGranted('ROLE_WS_CREATOR')){
            $user = $this->get('security.context')->getToken()->getUser();
            $dirs = $this->get('simple_it.exercise.directory')->allParents($user);
        }
        $teachers = $this->get('simple_it.exercise.user')->allTeachers();
        foreach($dirs as $key => $dir){
            $dirs[$key]['currentStudents'] = $this
                ->get('simple_it.exercise.directory')
                ->countCurrentStudents($dir['id'], array_column($teachers,"id"))[0]['total'];
            $dirs[$key]['oldStudents'] = $this
                ->get('simple_it.exercise.directory')
                ->countOldStudents($dir['id'], array_column($teachers,"id"))[0]['total'];

        }
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:stat.html.twig', array(
                'dirs' => $dirs,
            )
        );
    }

    public function fullFillAction(Directory $directory, StatView $view = null)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (
            $this->get('security.context')->isGranted('ROLE_ADMIN')
        ){
            if ($view == null){
                $view = $directory->getLastView();
            }
            // every users connected between frame time
            $count = 0;
            #foreach($directory->realUsers() as $user){
            #    if ($user->isOnlyStudent()){
            #        foreach($user->getLogs() as $log){
            #            if ($log->getLoggedAt() >= $view->getStartDate()
            #                && $log->getLoggedAt() <= $view->getEndDate()
            #            ){
            #                echo "user: " . $user->getUsername(). " at ".
            #                    $log->getLoggedAt()->format('Y-m-d H:i:s'). "<br>";
            #                $count++;
            #                break;
            #            }
            #        }
            #    }
            #}
            echo "directory : " . $directory->getId()."<br>";
            foreach($directory->getUsers() as $aud){
                if ($aud->getStartDate()->format('Y-m-d H:i:s') == "-0001-11-30 00:00:00" ){
                    $user = $aud->getUser();
                    if ($user->isOnlyStudent()){
                        foreach($user->getLogs() as $log){
                            if ($log->getLoggedAt() >= $view->getStartDate()
                                && $log->getLoggedAt() <= $view->getEndDate()
                            ){
                                echo "user: " . $user->getUsername(). " at ".
                                    $log->getLoggedAt()->format('Y-m-d H:i:s'). "<br>";
                                $count++;
                                $aud->setStartDate($log->getLoggedAt());
                            }
                        }
                    }
                }
            }
            $em = $this->getDoctrine()->getEntityManager()->flush();
            echo "au total : $count";
            die('!');
        }
    }
    public function statDirectoryAction(Directory $directory, StatView $view = null)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if ($directory->hasUser($user)
            || $this->get('security.context')->isGranted('ROLE_ADMIN')
        ){
            if ($view == null){
                $view = $directory->getLastView();
            }
            // every users connected between frame time
            $users = $this->get('simple_it.exercise.directory')->getIdUsers($directory, $view);
            $usernames = $this->get('simple_it.exercise.directory')->getUsernames($directory, $view);
            $directories = $this->get('simple_it.exercise.directory')->getModelStats($directory,$view, $users);
            $params = array(
                'directories' => $directories,
                'users' => count($users),
                'usernames' => $usernames
            );
            return $this->render(
                'SimpleITClaireExerciseBundle:Frontend:statdir.html.twig',$params
            );
        }
        return $this->redirectToRoute('admin_stats');
    }
    public function filterDirectoryAction(Directory $directory, StatView $view = null)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if ($directory->hasUser($user)
            || $this->get('security.context')->isGranted('ROLE_ADMIN')
        ){
            $this->get('simple_it.exercise.directory')->hasView($directory);
            if ($view == null){
                $view = $directory->getLastView();
            }
            $params = array(
                'directory' => $directory,
                'selectView' => $view,
                'createForm' => $this->createViewAction($directory),
            );
            if ($view){
                $params['editForm'] = $this->editViewAction($view);
            }
            return $this->render(
                'SimpleITClaireExerciseBundle:Frontend:filterdir.html.twig',$params
            );
        }
        return $this->redirectToRoute('admin_stats');
    }

    public function createViewAction(Directory $directory)
    {
        $request = $this->getRequest();
        $view = new StatView();
        $view->setDirectory($directory);
        $form = $this->createForm(StatViewType::class, $view,
            array(
                'action' => $this->generateUrl(
                    'admin_stats_create_view',
                    array('directory' => $directory->getId()))
            )
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($view);
            $em->flush();
            return $this->redirectToRoute('admin_filters_directory',
                array(
                    'directory' => $directory->getId(),
                    'view' => $view->getId()
                )
            );
        }
        return $form->createView();
    }
    public function editViewAction(StatView $view)
    {
        $request = $this->getRequest();
        $form = $this->createForm(StatViewType::class, $view,
            array(
                'action' => $this->generateUrl(
                    'admin_stats_edit_view',
                    array('view' => $view->getId()))
            )
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->flush();
            return $this->redirectToRoute('admin_filters_directory',
                array(
                    'directory' => $view->getDirectory()->getId(),
                    'view' => $view->getId()
            ));
        }
        return $this->render(
            "SimpleITClaireExerciseBundle:Form:lessForm.html.twig",
            array(
                'form' => $form->createView(),
            )
        );
        return $form->createView();
    }

    public function deleteViewAction(StatView $view)
    {
        try {
            $this->get('simple_it.exercise.stat_view')->remove(
                $view
            );

            return new ApiDeletedResponse();

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ResourceResource::RESOURCE_NAME);
        } catch (EntityDeletionException $ede) {
            throw new ApiBadRequestException($ede->getMessage());
        }
    }

}
