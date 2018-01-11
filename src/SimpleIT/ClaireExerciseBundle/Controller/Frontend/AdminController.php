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

namespace SimpleIT\ClaireExerciseBundle\Controller\Frontend;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use Symfony\Component\Security\Core\SecurityContext;
use SimpleIT\ClaireExerciseBundle\Form\AskerUserType;
use SimpleIT\ClaireExerciseBundle\Form\AskerPasswordType;
/**
 * Class AdminController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AdminController extends BaseController
{
    public function indexAction()
    {
        $dirs = $this->get('simple_it.exercise.directory')->allParents();
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:admin.html.twig', array(
                'dirs' => $dirs,
            )
        );
    }
    public function statsDirectoryAction(Directory $directory)
    {
        $directories = $this->get('simple_it.exercise.directory')->getModelStats($directory);
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:adminstats.html.twig', array(
                'directories' => $directories,
            )
        );
    }

    public function listDisableAction()
    {
        $dirs = $this->get('simple_it.exercise.directory')->all();
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:list.html.twig',
            array(
                'users' => $this->get('simple_it.exercise.user')->allDisabled(),
                'dirs' => $dirs,
            )
        );
    }
    public function allAction()
    {
        $dirs = $this->get('simple_it.exercise.directory')->all();
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:list.html.twig',
            array(
                'users' => $this->get('simple_it.exercise.user')->getAll(),
                'dirs' => $dirs,
            )
        );
    }
    public function editAction(AskerUser $user)
    {
        $request = $this->getRequest();
        $form = $this->createForm(AskerUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->flush();
            return $this->redirectToRoute('admin_list_users');
        }
        return $this->render(
            "SimpleITClaireExerciseBundle:Form:form.html.twig",
            array(
                'form' => $form->createView(),
            )
        );
    }
    public function updatePasswordAction(AskerUser $user)
    {
        $request = $this->getRequest();
        $form = $this->createForm(AskerPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $user->setPassword(
                password_hash($user->getPassword(), PASSWORD_DEFAULT)
            );
            $em->flush();
            return $this->redirectToRoute('admin_list_users');
        }
        return $this->render(
            "SimpleITClaireExerciseBundle:Form:form.html.twig",
            array(
                'form' => $form->createView(),
            )
        );
    }

    public function changeAction()
    {
        $request =  $this->get('request');
        if ($request->get('usersCheck') !== null){
            $userService = $this->get('simple_it.exercise.user');
            $em = $this->getDoctrine()->getEntityManager();
            if ($request->get('delete')!==null){
                foreach($request->get('usersCheck') as $checked ){
                    $user = $userService->get($checked);
                    $em->remove($user);
                }
            }else{
                $roleService = $this->get('simple_it.exercise.role');
                $directoryService = $this->get('simple_it.exercise.directory');
                $roleUser = $roleService->getRoleUser();
                $dir = $directoryService->find(
                    $request->get('directory')
                );
                foreach($request->get('usersCheck') as $checked ){
                    $user = $userService->get($checked);
                    $user->addRole($roleUser);
                    $user->setIsEnable(1);
                    $dir->addUser($user);
                }
            }
            $em->flush();
        }
        return $this->redirectToRoute($request->get('current'));
    }


}
