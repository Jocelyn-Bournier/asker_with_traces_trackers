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
use SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory;
use SimpleIT\ClaireExerciseBundle\Entity\Directory;
use SimpleIT\ClaireExerciseBundle\Entity\Pedagogic;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function showAURAction(AskerUser $user)
    {
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:popover_aur.html.twig',
            array(
                'aurs' => $this->get('simple_it.exercise.asker_user_directory')->getArrayByUser($user)
            )
        );
        return new JsonResponse($this->get('simple_it.exercise.asker_user_directory')->getArrayByUser($user));

    }

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
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:template_creator.html.twig'
        );
    }

    public function listDisableAction()
    {
        $dirs = $this->get('simple_it.exercise.directory')->allParents();
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:list_users.html.twig',
            array(
                'users' => $this->get('simple_it.exercise.user')->allDisabled(),
                'dirs' => $dirs,
            )
        );
    }
    public function allAction()
    {
        $dirs = $this->get('simple_it.exercise.directory')->nativeParents();
        return $this->render(
            'SimpleITClaireExerciseBundle:Frontend:list_users.html.twig',
            array(
                'users' => $this->get('simple_it.exercise.user')->getNativeAll(),
                'dirs' => $dirs,
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
                    $dirUser = new AskerUserDirectory();
                    $user->addRole($roleUser);
                    $user->setIsEnable(1);
                    #$dir->addUser($user);
                    $dirUser->setUser($user);
                    $dirUser->setIsManager(false);
                    $dirUser->setDirectory($dir);
                    $em->persist($dirUser);
                }
            }
            $em->flush();
        }
        return $this->redirectToRoute($request->get('current'));
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

            // We add profile if the directory is linked with a framework
            foreach($user->getDirectories() as $dir){
                if ($originalDirectories->contains($dir) === false
                    && $dir->getDirectory()->getOwner()->getId() !== $user->getId() && $dir->getDirectory()->getFrameworkId() !== null
                ){
                    $response = $this->addComperToUser($dir->getDirectory()->getFrameworkId(), $user->getId());
                }

            }

            $this->get('simple_it.exercise.asker_user_directory')->deleteChildrens($user, $deleted);
            $this->get('simple_it.exercise.asker_user_directory')->updateForUser($user);

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
    public function importLocalAction(){
        $handle = fopen(__DIR__."/datas.csv", "r");
        $datas=array();
        $request = $this->getRequest();
        $role = "ROLE_USER";
        $userService = $this->get('simple_it.exercise.user');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $exploded =  explode(';', $line);
                $username = "ext_".$exploded[1].".".$exploded[0];
                $user = $userService->createLocalUser(
                    $exploded[1],
                    $exploded[0],
                    $username,
                    $exploded[3],
                    True
                );
                if ($this->get('simple_it.exercise.role')->addRoleToUser($role, $user)){
                    $this->get('simple_it.exercise.asker_user_directory')->create(
                        $user,
                        $this->get('simple_it.exercise.directory')
                            ->findOneByName('M1101 - Programmation Shell')
                    );
                }else{
                    die("Role: $role does not exist");

                }
                $newLine = trim($line).";$username;".$exploded[3];
                $datas[] = $newLine;
            }
            fclose($handle);
            $handle = fopen(__DIR__."/newdatas.csv", "w");
            foreach($datas as $line){
                fwrite($handle,$line."\n");
            }
            fclose($handle);
        } else {
            die("the file does not exist");
        }
    }

    public function addComperToUser($frameworkId, $userId){
        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->container->get('simple_it.exercise.user');
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+30;
        $payload    = [
            "user"     => "asker:".$userId,
            "fwid"     => intval($frameworkId),
            "username" => $user->get($userId)->getUsername(),
            "role"     => 'learner',
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/'
        ];

        $token = $jwtEncoder->getToken($payload);

        $profileService = $this->container->get('app.profileService');
        $profile = new JsonResponse($profileService->createProfile($token));

        //return $profile;
    }

}
