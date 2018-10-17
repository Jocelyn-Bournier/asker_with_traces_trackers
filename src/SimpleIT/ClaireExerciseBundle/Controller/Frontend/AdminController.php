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
use SimpleIT\ClaireExerciseBundle\Entity\Pedagogic;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\SecurityContext;
use SimpleIT\ClaireExerciseBundle\Form\AskerUserDirectoryType;
use SimpleIT\ClaireExerciseBundle\Form\AskerUserType;
use SimpleIT\ClaireExerciseBundle\Form\AskerPasswordType;
/**
 * Class AdminController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AdminController extends BaseController
{

    public function previewAction(AskerUser $user = null)
    {
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:preview.html.twig',
            array('preview' => $user)
        );
        return new Response($user->getUsername());
    }

    public function indexAction()
    {
        $dirs = array();
	#$user = $this->getDoctrine()->getRepository("SimpleITClaireExerciseBundle:AskerUserDirectory")
	#	->find(706);
	#die($user->getEndDate()->format('Y-m-d H:i:s'));
	#$user->setEndDate(new \DateTime("-0001-11-30 00:00:00"));
	#$this->getDoctrine()->getEntityManager()->flush();
	#$user = $this->getDoctrine()->getRepository("SimpleITClaireExerciseBundle:AskerUserDirectory")
	#	->find(866);
	#$user->setEndDate();
	#$this->getDoctrine()->getEntityManager()->flush();
	#die($user->getEndDate()->format('Y-m-d H:i:s'));
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:admin.html.twig', array(
                'dirs' => $dirs,
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
        //load old datas before binding
        $originalDirectories = new ArrayCollection();
        foreach ($user->getDirectories() as $aud) {
            $originalDirectories->add($aud);
        }
        $form = $this->createForm(AskerUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $deleted = array();
            foreach($originalDirectories as $aud){
                if ($user->getDirectories()->contains($aud) === false
                && $aud->getDirectory()->getOwner()->getId() !== $user->getId()
                ){
                    $deleted[] = $aud->getDirectory();
                    $em->remove($aud);
                }
            }
            $this->get('simple_it.exercise.asker_user_directory')->deleteChildrens($user, $deleted);
            $this->get('simple_it.exercise.asker_user_directory')->updateForUser($user);
            #$var=var_dump($deleted);
            #return new Response(
            #    "<html><body>$var </body></html>"
            #);

            $em->flush();
            return $this->redirectToRoute('admin_list_users');
        }
        return $this->render(
            "SimpleITClaireExerciseBundle:Form:user.html.twig",
            array(
                'form' => $form->createView(),
                'user' => $user
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
