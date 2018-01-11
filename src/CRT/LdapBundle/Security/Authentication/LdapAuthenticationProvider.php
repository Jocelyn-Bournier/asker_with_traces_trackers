<?php


namespace CRT\LdapBundle\Security\Authentication;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use CRT\LdapBundle\Security\Server\LdapServer;
use CRT\LdapBundle\Security\Server\LdapException;
use SimpleIT\ClaireExerciseBundle\Entity\Log;
use Doctrine\ORM\EntityManager;


class LdapAuthenticationProvider extends OwnAuthenticationProvider
{
    private $encoderFactory;
    private $userProvider;
    private $ldap;

    public function __construct(UserProviderInterface $userProvider,
                                UserCheckerInterface $userChecker, $providerKey,
                                EncoderFactoryInterface $encoderFactory,
                                $hideUserNotFoundExceptions = true,
                                LdapServer $ldap,
                                EntityManager $em)
    {
        parent::__construct($userChecker, $providerKey, $hideUserNotFoundExceptions);

        $this->encoderFactory = $encoderFactory;
        $this->userProvider = $userProvider;
        $this->ldap = $ldap;
        $this->em = $em;
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        try{
            if (method_exists ($user,'getIsLdap')){
                if ($user->getIsLdap()){
                    $this->ldap->checkPassword($user->getLdapDn(), $token->getCredentials());
                }else{
                    $currentUser = $token->getUser();
                    if ($currentUser instanceof UserInterface) {
                        if ($currentUser->getPassword() !== $user->getPassword()) {
                            throw new BadCredentialsException('The credentials were changed from another session.');
                        }
                    } else {
                        if ('' === ($presentedPassword = $token->getCredentials())) {
                            throw new BadCredentialsException('The presented password cannot be empty.');
                        }

                        if (!$this->encoderFactory
                            ->getEncoder($user)
                            ->isPasswordValid(
                                $user->getPassword(),
                                $presentedPassword,
                                $user->getSalt()
                            )
                        ) {
                            throw new BadCredentialsException('The presented password is invalid.');
                        }
                        if (!$user->getIsEnable()){
                            throw new BadCredentialsException('This user is not enable');
                        }
                    }
                }
            }
            //if ($user->isLdap()){
            //    $this->ldap->checkPassword($user->getLdapDn(), $token->getCredentials());
            //}else{
            //    $currentUser = $token->getUser();
            //    if ($currentUser instanceof UserInterface) {
            //        if ($currentUser->getPassword() !== $user->getPassword()) {
            //            throw new BadCredentialsException('The credentials were changed from another session.');
            //        }
            //    } else {
            //        if ('' === ($presentedPassword = $token->getCredentials())) {
            //            throw new BadCredentialsException('The presented password cannot be empty.');
            //        }

            //        if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
            //            throw new BadCredentialsException('The presented password is invalid.');
            //        }
            //    }
            //}
            //$this->ldap->checkPassword($user->getUid(), $token->getCredentials());
        }catch(LdapException $e){
            throw new BadCredentialsException('Bad Credentials.');
        }
        $log = new Log($user);
        $this->em->persist($log);
        $this->em->flush();
    }

    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            return $user;
        }

        try {
            $user = $this->userProvider->loadUserByUsername($username);
            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
            }
            return $user;
        } catch (UsernameNotFoundException $notFound) {
            $notFound->setUsername($username);
            throw $notFound;
        } catch (\Exception $repositoryProblem) {
            $ex = new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
            $ex->setToken($token);
            throw $ex;
        }
    }
}
