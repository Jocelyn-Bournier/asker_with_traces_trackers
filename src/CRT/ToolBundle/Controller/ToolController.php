<?php

namespace CRT\ToolBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

use CRT\ToolBundle\Form\RequestType;
use CRT\ToolBundle\Entity\Request;

class ToolController extends Controller
{
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
        return $this->render('CRTToolBundle:Content:login.html.twig', array(
            // last username entered by the user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }
    public function accountRequestAction()
    {
        //variable
        $exit = 'FAILED';
        $def = $this->get('crt_tool.definitions');
        $action = "to ask an account";
        $ip = $this->get('request')->getClientIp();
        $username = "unknown";

        $academies = $this->getDoctrine()
            ->getRepository('CRTToolBundle:Academy')
            ->findPreferred()
        ;
        $status = $this->getDoctrine()
            ->getRepository('CRTToolBundle:RequestStatus')
            ->findOneByLabel($def->getWaiting())
        ;
        $type = new RequestType($academies);
        $requestObject = new Request();
        $form = $this->createForm($type, $requestObject);
        $request = $this->get('request');


        $form->handleRequest($request);
        if ($request->getMethod() == 'POST'){
            if ($form->isValid()){
                $data = $form->getData();
                try{
                    $ema = $this->getDoctrine()
                        ->getManager()
                    ;
                    $requestObject->setCreateDate(new \DateTime());
                    $requestObject->setToken(sha1(uniqid()));
                    $requestObject->setStatus($status);
                    $ema->persist($requestObject);
                    $ema->flush();
                }catch(\Doctrine\DBAL\DBALException $e){
                    die('Une erreur SQL est survenue :<');
                }

                $duration = $this->container->getParameter('request.duration');
                $destinataires = $this->container->getParameter('adminAddresses');
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->container->getParameter('email.subject.confirm'))
                    ->setFrom('no-reply@phm.education.gouv.fr')
                    ->setTo($requestObject->getEmail())
                    ->setBody(
                        $this->renderView(
                            'Email/checkRequest.txt.twig',
                            array(
                                'firstName' => $requestObject->getFirstName(),
                                'lastName' => $requestObject->getLastName(),
                                'email' => $requestObject->getEmail(),
                                'academy' => $requestObject->getAcademy()->getLabel(),
                                'corporate' => $requestObject->getCorporate()->getLabel(),
                                'title' => $requestObject->getTitle(),
                                'deskPhone' => $requestObject->getDeskPhone(),
                                'mobiPhone' => $requestObject->getMobiPhone(),
                                'duration' => $duration,
                                'confirmLink' => $this->generateUrl(
                                    'crt_tool_confirm_token',
                                    array(
                                        'token' => $requestObject->getToken(),
                                    ),
                                    true
                                )
                            )
                        ),
                        'text/plain'
                    )
                ;
                $username = $requestObject->getFirstName() . ' ' .
                    $requestObject->getLastName(). " (".$requestObject->getEmail().
                    ")"
                ;
                $sent = $this->get('mailer')->send($message);
                if ($sent){
                    $exit = 'SUCCEED';
                    $this->writeAskAccount($ip, $exit, $username, $action);
                    return $this->render(
                        'CRTToolBundle:Content:sendConfirm.html.twig',
                        array(
                            'email' => $requestObject->getEmail(),
                            'error' => 0,
                        )
                    );
                }else{
                    return $this->render(
                        'CRTToolBundle:Content:sendConfirm.html.twig',
                        array(
                            'email' => $requestObject->getEmail(),
                            'error' => 1,
                        )
                    );
                }
            }
            $this->writeAskAccount($ip, $exit, $username, $action);
        }
        return $this->render('CRTToolBundle:Content:account.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    public function requestConfirmAction($token)
    {
        /* 1 => expired
        ** 2 => email error
        ** 3 => already confirmed
        ** 0 => ok
        */
        $def = $this->get('crt_tool.definitions');
        $request = $this->getDoctrine()
            ->getRepository('CRTToolBundle:Request')
            ->findOneByToken($token)
        ;
        $dateLog = new \DateTime();
        $dateString = $dateLog->format('d-m-Y H:i:s');
        $ip = $this->get('request')->getClientIp();
        $exit = 'FAILED';
        $notice = 0;
        if($request){
            $username = $request->getFirstName(). ' '. $request->getLastName().
                " (". $request->getEmail().")";
            //On va chercher le temps de validité dans Resources/config/services.yml
            $validity = $this->container->getParameter('requestValidity');
            $now = new \DateTime();
            $createDate = new\DateTime($request->getCreateDate()->format('d-m-Y h:i:s'));
            $validDate = $createDate->add(new \DateInterval($validity));

            //si deja confirme
            if ($request->getStatus()->getLabel() == $def->getConfirmed()){
                $exit = 'CONFIRMED';
                $this->writeAskAccount($ip, $exit, $username, ' again the request');
                return $this->render(
                    'CRTToolBundle:Content:confirmReceived.html.twig',
                    array(
                        'error' => 3,
                    )
                );
            }
            //Si la demande est toujours valide
            if ($validDate > $now){
                // on fait un update uniquement si l'utilisateur n a jamais confirme
                if ($request->getStatus()->getLabel() != $def->getConfirmed()){
                    $notice = 1;
                    try{
                        $status = $this->getDoctrine()
                            ->getRepository('CRTToolBundle:RequestStatus')
                            ->findOneByLabel($def->getConfirmed())
                        ;
                        $request->setStatus($status);
                        $ema = $this->getDoctrine()
                            ->getManager()
                        ;
                        $ema->flush();
                    }catch(\Doctrine\DBAL\DBALException $e){
                        die('Une erreur SQL est survenue :<');
                    }
                }
            //sinon elle n'est plus valide
            }else{
                if ($request->getStatus()->getLabel() != $def->getExpired()){
                    try{
                        $expired = $this->getDoctrine()
                            ->getRepository('CRTToolBundle:RequestStatus')
                            ->findOneByLabel($def->getExpired())
                        ;
                        $request->setStatus($expired);
                        $ema = $this->getDoctrine()
                            ->getManager()
                        ;
                        $ema->flush();
                    }catch(\Doctrine\DBAL\DBALException $e){
                        die('Une erreur SQL est survenue :<');
                    }
                }//else aucune modification
                $exit = 'FAILED';
                $this->writeAskAccount($ip, $exit, $username, ' to confirm before the delay');
                return $this->render(
                    'CRTToolBundle:Content:confirmReceived.html.twig',
                    array(
                        'error' => 1,
                    )
                );
            }
            if ($notice == 1){
                $destinataires = $this->container->getParameter('adminAddresses');
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->container->getParameter('email.subject.request'))
                    ->setFrom('no-reply@phm.education.gouv.fr')
                    ->setTo($destinataires)
                    ->setBody(
                        $this->renderView(
                            'Email/accountRequest.txt.twig',
                            array(
                                'firstName' => $request->getFirstName(),
                                'lastName' => $request->getLastName(),
                                'email' => $request->getEmail(),
                                'academy' => $request->getAcademy()->getLabel(),
                                'corporate' => $request->getCorporate()->getLabel(),
                                'title' => $request->getTitle(),
                                'deskPhone' => $request->getDeskPhone(),
                                'mobiPhone' => $request->getMobiPhone(),
                            )
                        ),
                        'text/plain'
                    )
                ;
                $exit = 'SUCCEED';
                $this->writeAskAccount($ip, $exit, $username, ' to confirm');
                $sent = $this->get('mailer')->send($message);
                if ($sent){
                    return $this->render(
                        'CRTToolBundle:Content:confirmReceived.html.twig',
                        array(
                            'error' => 0,
                        )
                    );
                }else{
                    return $this->render(
                        'CRTToolBundle:Content:confirmReceived.html.twig',
                        array(
                            'error' => 2,
                        )
                    );
                }
            }
        }else{
            $message = "$dateString:[$ip]: tried to confirm this".
            " imagniary token : $token\n";
            $this->writeDanger($message);
            $destinataires = $this->container->getParameter('adminAddresses');
            $message = \Swift_Message::newInstance()
                ->setSubject($this->container->getParameter('email.subject.danger'))
                ->setFrom('no-reply@phm.education.gouv.fr')
                ->setTo($destinataires)
                ->setBody(
                    $this->renderView(
                        'Email/danger.txt.twig',
                        array(
                            'token' => $token,
                            'date' => $dateString,
                            'ip' => $ip,
                        )
                    ),
                    'text/plain'
                )
            ;
            $this->get('mailer')->send($message);
            return $this->redirect($this->generateUrl('login'));
        }
    }

    public function dashBoardAction()
    {
        $roleAdmin = $this->container->getParameter('crtres_admins');
        if ($this->get('security.context')->isGranted($roleAdmin)) {
            return $this->render(
                'CRTToolBundle:Content:dashBoard.html.twig'
            );
        }else{
            return $this->redirect($this->generateUrl('crt_tool_password_update'));
        }
    }

    public function passwordUpdateAction()
    {
        $request = $this->get('request');
        if ($request->getMethod() == 'POST'){
            $username = $this->get('security.context')->getToken()->getUser()->getUsername();
            $params = $request->request->all();

            $regCriteres = "/((?=.*[a-z])(?=.*[A-Z])(?=.*\\d)|(?=.*[a-z])(?=.*\\d)(?=.*\\W)|(?=.*[a-z])(?=.*[A-Z])(?=.*\\W)|(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)|(?=.*[A-Z])(?=.*\\d)(?=.*\\W])){8,}/";
            $regAllowed = "/^(.*[a-zA-Z\\d\\W]{8,})$/";
            //Ajouter un regForbiden et ajouter dans le IF

            $current = $params['_current_password'];
            $new = $params['_new_password'];
            $check = $params['_check_password'];

            $ip = $this->get('request')->getClientIp();
            $exit = 'FAILED';
            if ($new === $check ){
                if (
                    preg_match($regCriteres, $new) &&
                    preg_match($regAllowed, $new)
                ){
                    $ldap = $this->get('ldap_server');
                    $updated = $ldap->updatePassword(
                        $username,
                        $current,
                        $new
                    );
                    if($updated == 2){
                        $this->get('session')->getFlashBag()->add(
                            'error-current',
                            "Le mot de passe actuel n'est pas correct."
                        );
                    }else if ($updated == 1){
                        $exit = 'SUCCEED';
                        $this->get('session')->getFlashBag()->add(
                            'success',
                            'Votre mot de passe à été changé avec succès.'
                        );
                    }else if ($updated == 0){
                        die ('Une erreur est survenue');
                    }
                }else{
                    $this->get('session')->getFlashBag()->add(
                        'error-match',
                        "Le mot de passe ne valide pas trois critères."
                    );
                }
            }else{
                    $this->get('session')->getFlashBag()->add(
                        'error-match',
                        "Les deux mots passe ne correspondent pas."
                    );
            }
            $this->writeUpdatePassword($ip, $exit, $username);
        }
        return $this->render('CRTToolBundle:Content:passwordUpdate.html.twig');
    }
    public function showUserAttributsAction($user = null)
    {
        $values = null;
        $error = null;
        if ($user !== null){
            $ldap = $this->get('ldap_server');
            $filter = "(&(uid=$user)(objectClass=account))";
            $values = $ldap->ldapParse(array(), $ldap->getBaseUser(), $filter);
            if ($values['count'] != 1){
                if ($values['count'] == 0){
                    $error = 1;
                }else{
                    $error = 2;
                }
                $values = null;
            }
        }
        return $this->render('CRTToolBundle:Content:showUserAttributs.html.twig',
            array(
                'user' => $user,
                'error' => $error,
                'values' => $values
            )
        );
    }

    public function showRequestAction()
    {
        $requests = $this->getDoctrine()
            ->getRepository('CRTToolBundle:Request')
            ->findBy(
                array(),
                array(
                    'createDate' => 'desc',
                )
            )
        ;
        return $this->render('CRTToolBundle:Content:showRequest.html.twig',
            array(
                'requests' => $requests
            )
        );
    }

    private function writeDanger($message)
    {
        $logFile = fopen('/var/log/crttool/unauthorizedAction.log', 'a');
        fwrite($logFile, $message);
    }

    private function writeUpdatePassword($ip, $status, $user)
    {
        $date = new \DateTime();
        $dateString = $date->format('d-m-Y H:i:s');
        $logFile = fopen('/var/log/crttool/updatePassword.log', 'a');
        fwrite($logFile, "$dateString:[$ip]:$user $status to change $user's password\n");
    }

    private function writeAskAccount($ip, $status, $user, $action)
    {
        $date = new \DateTime();
        $dateString = $date->format('d-m-Y H:i:s');
        $logFile = fopen('/var/log/crttool/askAccount.log', 'a');
        fwrite($logFile, "$dateString:[$ip]:$user $status $action\n");
    }
}
