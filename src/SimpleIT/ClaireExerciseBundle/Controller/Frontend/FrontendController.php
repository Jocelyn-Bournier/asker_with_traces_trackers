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
use Symfony\Component\Security\Core\SecurityContext;
use SimpleIT\ClaireExerciseBundle\Form\AskerPasswordType;
/**
 * Class FrontendController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class FrontendController extends BaseController
{
    /**
     * Render front application
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException
     */
    public function indexAction()
    {
        $userId = $this->getUserId();

        $form = $this->createForm(AskerPasswordType::class, $this->getUser(),
            array(
                'action' => $this->generateUrl('frontend_password')
            )
        );
        if ($this->get('security.context')->isGranted('ROLE_WS_CREATOR')) {
            return $this->render(
                'SimpleITClaireExerciseBundle:Frontend:manager-layout.html.twig',
                array('currentUserId' => $userId, 'form' => $form->createView())
            );
        } else {
            return $this->render(
                'SimpleITClaireExerciseBundle:Frontend:user-layout.html.twig',
                array('currentUserId' => $userId, 'form' => $form->createView())
            );
        }
    }
    public function updatePasswordAction()
    {
        $request = $this->getRequest();
        $user = $this->getUser();
        $form = $this->createForm(AskerPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $user->setPassword(
                password_hash($user->getPassword(), PASSWORD_DEFAULT)
            );
            $em->flush();
            $this->get('security.context')->setToken(null);
            $this->get('request')->getSession()->invalidate();
        }
        return $this->redirectToRoute('frontend_index');
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        return $this->render('SimpleITClaireExerciseBundle:Frontend:login.html.twig', array(
            // last username entered by the user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));

    }
    public function signAction()
    {
        $request = $this->getRequest();
        if ($request->isMethod('POST')){
            $user = new AskerUser();
            $user->setFirstName($request->get('firstName'));
            $user->setLastName($request->get('lastName'));
            $user->setUsername(
                "ext_".$request->get('firstName').
                ".".$request->get('lastName')
            );
            $user->setPassword(
                password_hash($request->get('password'), PASSWORD_DEFAULT)
            );
            $user->setLdapEmployeeId(0);
            $user->setIsLdap(0);
            $user->setIsEnable(0);
            $user->setLdapDn('');
            $user->setSalt(uniqid());
            $em = $this->getDoctrine()->getManager();
            try{
                $em->persist($user);
                $em->flush();
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', "La création du compte s'est réalisée avec succès. Un administrateur doit maintenant activer votre compte.")
                ;
            }catch(\Doctrine\DBAL\DBALException $e){
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', "Il y a eu une erreur lors de l'enregistrement de votre compte.")
                ;
            }
            return $this->redirectToRoute('login', array(), 301);
        }else{
            return $this->render(
                'SimpleITClaireExerciseBundle:Frontend:sign.html.twig'
            );
        }
    }
} 
