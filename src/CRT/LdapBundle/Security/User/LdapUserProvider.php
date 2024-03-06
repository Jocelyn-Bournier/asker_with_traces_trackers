<?php

namespace CRT\LdapBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use CRT\LdapBundle\Security\Server\LdapServer;
use CRT\LdapBundle\Security\Server\LdapException;
use SimpleIT\ClaireExerciseBundle\Entity\Pedagogic;
use SimpleIT\ClaireExerciseBundle\Entity\AskerUserDirectory;
use SimpleIT\ClaireExerciseBundle\Service\AskerUserDirectoryService;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;

//asker add
use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;

class LdapUserProvider implements UserProviderInterface
{

    private $ldap;
    private $em;
    private $refPassword;

    public function __construct(
        LdapServer $ldap,
        EntityManager $em,
        AskerUserDirectoryService  $audService,
        LoggerInterface $logger,
        string $refPassword)
    {
        $this->ldap = $ldap;
        $this->em = $em;
        $this->audService = $audService;
        $this->logger = $logger;
        $this->refPassword = $refPassword;
    }
    public function loadUserByUsername($username)
    {
        //Check if cn exists in LDAP
	    $lowUsername = strtolower($username);
        try{
            $uid = $this->ldap->searchUserDn($lowUsername);
        } catch (LdapException $e){
            throw new UsernameNotFoundException(sprintf('Cannot contact LDAP server and cannot check if %s exists', $lowUsername));
        }
        //If no dn this user doesn't exist
        if ($uid === false){
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $lowUsername));
        }else{
            $this->logger->info("User: $username try to connect");
            $dbUser = $this->em->getRepository('SimpleITClaireExerciseBundle:AskerUser')
                ->findOneBy(array('username' => $lowUsername))
            ;
            if (!$dbUser){
                $attributes = $this->ldap->getUserAttributes($lowUsername, array('employeeID'));
                $firstName = $this->ldap->getUserAttributes($lowUsername, array('givenName'));
                $lastName = $this->ldap->getUserAttributes($lowUsername, array('sn'));
                $userRole = $this->em
                    ->getRepository('SimpleITClaireExerciseBundle:Role')
                    ->findOneBy(array('name' => 'ROLE_USER')
                );
                $id = $attributes[0]['employeeid'][0];
                $user = new AskerUser();
                $user->setPassword(uniqid());
                $user->setSalt(uniqid());
                $user->setUsername($lowUsername);
                $user->setFirstName($firstName[0]['givenname'][0]);
                $user->setLastName($lastName[0]['sn'][0]);
                $user->setLdapDn($uid);
                $user->setLdapEmployeeId($id);
                $user->setIsLdap(1);
                $user->setIsEnable(1);
                $type = $this->ldap->getUserAttributes($lowUsername, array('gidNumber'));
                $user->addRole($userRole);
                if (isset($type[0]['gidnumber'])){
                    if ($type[0]['gidnumber'][0] == "2000"){
                        $creatorRole = $this->em
                            ->getRepository('SimpleITClaireExerciseBundle:Role')
                            ->findOneBy(array('name' => 'ROLE_WS_CREATOR')
                        );
                        $user->addRole($creatorRole);
                    }
                }
                try{
                    $this->em->persist($user);
                    $this->em->flush();
                }catch(\Exception $e){
                    $this->critical("Error cannot flush 82". $e->getMessage());
                }
                $dbUser = $user;
            }
            if ($dbUser->getLdapDn() !== $uid){
                $dbUser->setLdapDn($uid);
                try{
                    $this->em->flush();
                }catch(\Exception $e){
                    $this->critical("Error cannot flush 91". $e->getMessage());
                }
            }
            $db_username = "asker_read";
            $db_password = $this->refPassword;
            $conn = oci_connect($db_username, $db_password, 'bdref.univ-lyon1.fr:1526/REF');
            if (!$conn){
                $e = oci_error();
                $this->logger->error("Oracle error: {$e['message']}");
            }else{
		$this->logger->info("generating oci_parse query for $lowUsername");
                $stid = oci_parse($conn,"
                SELECT g.REF_CODE_CONNECTEUR,
                     pg.GRP_MILLESIME,
                     g.GRP_LIBELLE,
                     g.REF_ID_CONNECTEUR,
                     g.REF_TYPE_CONNECTEUR
                FROM REFERENTIEL.GROUPE g,
                     REFERENTIEL.PERSONNE p,
                     REFERENTIEL.PERSONNE_PROFIL pp,
                     REFERENTIEL.PERSONNE_GROUPE pg
                WHERE p.REF_ID = pp.REF_ID
                      AND p.REF_ID = pg.REF_ID
                      AND pp.REF_CODE_CONNECTEUR = 'APOG'
                      AND pp.REF_SAMACCOUNTNAME = '$lowUsername'
                      AND pg.GRP_ID = g.GRP_ID

                ");
                if(oci_execute($stid)){
		    $this->logger->info("OCI execution successed for $lowUsername");
                    $directories = $this->em
                        ->getRepository('SimpleITClaireExerciseBundle:Directory')
                        ->findAll()
                    ;
                    $aur = $this->em
                        ->getRepository('SimpleITClaireExerciseBundle:AskerUserDirectory')
                    ;
                    $pedaRepo = $this->em
                        ->getRepository('SimpleITClaireExerciseBundle:Pedagogic')
                    ;

                    $refData = array();
                    while($row = oci_fetch_array($stid)) {
                        $this->logger->info("Oracle: user $lowUsername, from REF: refcodeconnecter{$row[0]}-grp_millesime{$row[1]}-grp_livelle{$row[2]}-id_connecteur{$row[3]}");
                        if (isset($row[3])){
                            //si groupe APO, ça donc contenir PRINT ou AUTOM
                            if ($row[3] == "GRP-APO"){
                                if(preg_match(
                                    '/GP[12](PRINT|AUTOM)-[A-Z]{3}-[A-Z0-9]{7}[A-Z]/',
                                    $row[2]
                                )){
                                    $code = explode('-',$row[2])[2];
                                    if (!isset($refData[$code])){
                                            $refData[$code] = array();
                                            $refData[$code]['period'] = null;
                                            $refData[$code]['type'] = null;
                                    }
                                    $refData[$code]['year'] = $row[1];
                                    $refData[$code]['period'] =  $row[2];
                                }
                            //Si ELP APO ca contient le code APOGEE de notre pointdevue
                            }else if ($row[3] == "ELP-APO"){
                                if ($row[4] == "UE"){
                                    $code = $row[0];
                                    if (!isset($refData[$code])){
                                        $refData[$code] = array();
                                        $refData[$code]['period'] = null;
                                    }
                                    $refData[$code]['longName'] =  $row[2];
                                    $refData[$code]['year'] = $row[1];
                                    $refData[$code]['type'] = "UE";
                                }
                            }
                        }
                    }
                    $this->logger->info("User: $lowUsername has " . oci_num_rows($stid). " rows in REF");
                    if(preg_match('/p[0-9]{7}/', $dbUser->getUsername())){
                        $this->logger->info("User:".  $dbUser->getUsername(). " match student regexp");
                        foreach($directories as $dir){
                            if (array_key_exists($dir->getCode(),$refData)){
                                $this->logger->info("User: $lowUsername match ". $dir->getName());
                                $isRegistered = $aur
                                    ->findOneBy(
                                        array(
                                            'directory' => $dir->getId(),
                                            'user' => $dbUser->getId(),
                                        )
                                   )
                                ;
                                if (!$isRegistered){
                                    $this->logger->info("User: $lowUsername has been added to" . $dir->getName());
                                    $userDir = new AskerUserDirectory();
                                    $userDir->setUser($dbUser);
                                    $userDir->setIsManager(false);
                                    $userDir->setIsReader(false);
                                    $userDir->setDirectory($dir);
                                    $dbUser->addDirectory($userDir);
                                    $dir->addUser($userDir);
                                    $this->em->persist($userDir);
                                }else{
                                    $this->logger->info("User: $lowUsername already has " . $dir->getName());
                                    if (!is_null($isRegistered->getEndDate())){
                                        if ($isRegistered->getEndDate()->format('Y-m-d H:i:s') !== "-0001-11-30 00:00:00"){
                                            $this->logger->info("User: $lowUsername is repeating UE:". $dir->getName());
                                            $isRegistered->setEndDate();
                                        }
                                    }
                                }
                            }
                        }
                        $this->audService->updateForUser($dbUser);
                        foreach($refData as $key => $value){
                            if ($value['type'] == "UE"){
                                $isSaved = $pedaRepo
                                    ->findOneBy(
                                        array(
                                            'code' => $key,
                                            'period' => $value['period'],
                                            'year' => $value['year']
                                        )
                                    )
                                ;
                                if(!$isSaved){
                                    $pedagogic = new Pedagogic();
                                    $pedagogic->setCode($key);
                                    $pedagogic->setPeriod($value['period']);
                                    $pedagogic->setYear($value['year']);
                                    $pedagogic->setLongName($value['longName']);
                                    $dbUser->addPedagogic($pedagogic);
                                    $this->logger->info("User: $lowUsername trigger a new pedagogic:". $pedagogic->getCode() );
                                    $this->em->persist($pedagogic);
                                }else{
                                    if (!$dbUser->getPedagogics()->contains($isSaved)){
                                        $this->logger->info("User: $lowUsername does not have pedagogic ". $isSaved->getCode());
                                        $dbUser->addPedagogic($isSaved);
                                    }else{
                                        $this->logger->info("User: $lowUsername has pedagogic ". $isSaved->getCode());
                                    }

                                }
                            }
                        }
                    }else{
                        $this->logger->info("User: $lowUsername does not match student regexp");
                    }
                    try{
                        $this->em->flush();
                    }catch(\Exception $e){
                        $this->critical("Error cannot flush 239". $e->getMessage());
                    }
				} else {
					$this->logger->info("OCI execution failed for $lowUsername");
				}
            }
            return $dbUser;
        }
    }

    public function refreshUser(UserInterface $user)
    {
        //if (!$user instanceof LdapUser) {
        if (!$user instanceof AskerUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'SimpleIT\ClaireExerciseBundle\Entity\AskerUser';
        //return $class === 'CRT\LdapBundle\Security\User\LdapUser';
    }
}
