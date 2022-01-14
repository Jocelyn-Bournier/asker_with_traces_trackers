<?php

namespace CRT\LdapBundle\Security\Server;


class LdapServer
{
    private $address;
    private $bindDn;
    private $bindPass;
    private $baseUser;
    private $baseGroup;
    private $port;
    private $resource;
    private $userFilter;
    private $groupFilter;
    private $roleMethod;
    private $roleAttributs;

    public function __construct($address, $port, $bindUser, $bindPass,
                                $baseUser, $userFilter,
                                $roleMethod,
                                $baseGroup = null, $groupFilter = null,
                                $roleAttributs = null)
    {
        $this->setAddress($address);
        $this->setPort($port);
        $this->setBindDn($bindUser);
        $this->setBindPass($bindPass);
        $this->setBaseUser($baseUser);
        $this->setUserFilter($userFilter);
        $this->setRoleMethod($roleMethod);
        if ($roleMethod == 0){
            if (
                is_null($baseGroup) ||
                is_null($groupFilter) ||
                empty($baseGroup) ||
                empty($groupFilter)
            ){
                throw new LdapException("When roleMethod is equal to 0 you ".
                    "have to define baseGroup and groupFilter");
            }else{
                $this->setBaseGroup($baseGroup);
                $this->setGroupFilter($groupFilter);
            }
        }else if ($roleMethod == 1){
            if (
                is_null($roleAttributs) ||
                empty($roleAttributs)
            ){
                throw new LdapException("When roleMethod is equal to 1 you ".
                    "have to define roleAttributs");
            }else{
                foreach($roleAttributs as $value){
                    if (is_null($value) || empty($value)){
                        throw new LdapException("You have to set the value of ".
                            "your attributs in roleAttributs");
                    }
                }
                $this->setRoleAttributs($roleAttributs);
            }
        }else{
                throw new LdapException("Role Method can be equal to 1 or 0");
        }
    }

    public function __toString(){
        return "Hi i'm your Ldap Server!";
    }

    public function updatePassword($username,$oldPassword, $newPassword)
    {
        $dn = $this->searchUserDn($username);
        try{
            $this->connect($dn, $oldPassword);
        }catch(LdapException $e){
            if (strstr($e, 'failed LDAP Authentification')){
                return 2;
            }
        }
        $password['userPassword'] = "{SHA}" . base64_encode( pack( "H*", sha1( $newPassword ) ) );
        if(ldap_modify($this->getResource(), $dn, $password)){
            $this->disconnect();
            return 1;
        }
        return 0;
    }
    private function connect($user, $password){
        if ($this->getResource() == null){
            $init = ldap_connect($this->getAddress(), $this->getPort());
            if (!$init){
                throw new LdapException("Unable to connect to ". $this->getAddress());
            }else{
                $this->setResource($init);
            }
            ldap_set_option($init, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($init, LDAP_OPT_REFERRALS, 0);
            $bind = @ldap_bind($this->getResource(),
                                $user,
                                $password);
            if(!$bind){
                if ($user == $this->getBindDn()){
                    $user = "Software's bind";
                }else{
                    $user = $this->getUidFromDn($user);
                }
                throw new LdapException("$user failed LDAP Authentification");
            }
        }
        return true;
    }

    private function disconnect(){
        if ($this->getResource() != null){
            ldap_unbind($this->getResource());
            $this->setResource(null);
        }
    }

    private function getCnFromDn($dn){
        $split = explode(',', $dn);
        if (strstr($split[0], "cn=")){
            $old = array('cn=', '-');
            $new = array('', '_');
            return str_replace($old, $new, $split[0]);
        }
        return false;
    }

    private function getUidFromDn($dn){
        $split = explode(',', $dn);
        if (strstr($split[0], "uid=")){
            $old = array('uid=', '-');
            $new = array('', '_');
            return str_replace($old, $new, $split[0]);
        }
        return false;
    }

    private function update($username, $string)
    {
       return str_replace('!_username_!', $username, $string);
    }

    public function ldapParse($ask, $base, $filter)
    {
        if ($this->getResource() == null){
            if(!$this->connect($this->getBindDn(), $this->getBindPass())){
                throw new LdapException('Check your LDAP configuration');
            }
        }
        $result = @ldap_search($this->getResource(),
                                $base,
                                $filter,
                                $ask
                            );
        if ($result === false){
            throw new LdapException('Something wrong happened. You should check your LDAP filter');
        }
        $values = ldap_get_entries($this->getResource(), $result);
        $this->disconnect();
        return $values;

    }

    public function searchUserDn($username)
    {
        if ($this->connect($this->getBindDn(), $this->getBindPass())){
            if ($this->getResource() == null){
            }
            $askedAttributes = array ('dn');
            $filter = $this->update($username, $this->getUserFilter());
            $info = $this->ldapParse($askedAttributes,$this->getBaseUser(), $filter);
            if ($info['count'] == 1){
                $this->disconnect();
                return $info[0]['dn'];
                //return htmlentities($info[0]['dn']);
            }else{
                return false;
            }
        }
        return false;
    }


    public function getUserAttributes($username, $arrayAttributes)
    {
        if ($this->connect($this->getBindDn(), $this->getBindPass())){
            $filter = $this->update($username, $this->getUserFilter());
            $info = $this->ldapParse($arrayAttributes,$this->getBaseUser(), $filter);
            if ($info['count'] ==1){
                return $info;
            }else{
                //Should never happen
                throw new LdapException("This $username returned more than 1 result");
            }
        }
    }

    private function in_array_value($need, $array){
        foreach($array as $value){
            if ($need === $value){
                return true;
            }
        }
        return false;
    }

    public function getRoles($username){
        $roles = array();
        if ($this->connect($this->getBindDn(), $this->getBindPass())){
            if ($this->getRoleMethod() == 0){
                $askedAttributes = array ('dn');
                $filter = $this->update($username, $this->getGroupFilter());
                $info = $this->ldapParse($askedAttributes,$this->getBaseGroup(), $filter);
                foreach($info as $key => $value){
                    if ($key !== "count"){
                        $roles[] = "ROLE_".strtoupper($this->getCnFromDn($value['dn']));
                    }
                }
            }else if ($this->getRoleMethod() == 1){
                $toParse = array();
                $stuffArray = array();
                foreach($this->getRoleAttributs() as $attribut => $stuff){
                    $toParse[] = $attribut;
                    if (is_array($stuff)){
                        foreach ($stuff as $subValue){
                            $stuffArray[$attribut][] = $subValue;
                        }
                    }else{
                        $stuffArray[$attribut][] = $stuff;
                    }
                }
                $filter = $this->update($username, $this->getUserFilter());
                $info = $this->ldapParse($toParse, $this->getBaseuser(), $filter);
                if ($info['count'] == 1){
                    foreach($info[0] as $key => $value){
                        if ($this->in_array_value($key, $toParse)){
                            foreach($info[0][$key] as $i => $v){
                                if ($i !== "count"){
                                    $temp = $stuffArray[$key];
                                    if ($this->in_array_value(
                                        $v, $temp
                                    )){
                                        $roles[] = "ROLE_".strtoupper(
                                            str_replace('-','_',$v)
                                        );
                                    }
                                }
                            }
                        }
                    }
                }else{
                    throw new LdapException("This $username returned more than 1 result");
                }
            }
            $this->disconnect();
            if (empty($roles)){
                $roles[] = "ROLE_USER";
            }
            return $roles;
        }
        return false;
    }


    public function checkPassword($dn, $password){
        if ($this->connect($dn, $password)){
            $this->disconnect();
        }
    }

    /**
    * Get baseUser.
    *
    * @return baseUser.
    */
    public function getBaseUser()
    {
       return $this->baseUser;
    }
    /**
    * Set baseUser.
    *
    * @param baseUser the value to set.
    */
    public function setBaseUser($baseUser)
    {
        $this->baseUser = $baseUser;
    }
    /**
    * Get bindPass.
    *
    * @return bindPass.
    */
    public function getBindPass()
    {
       return $this->bindPass;
    }
    /**
    * Set bindPass.
    *
    * @param bindPass the value to set.
    */
    public function setBindPass($bindPass)
    {
        $this->bindPass = $bindPass;
    }
    /**
    * Get bindDn.
    *
    * @return bindDn.
    */
    public function getBindDn()
    {
       return $this->bindDn;
    }
    /**
    * Set bindDn.
    *
    * @param bindDn the value to set.
    */
    public function setBindDn($bindDn)
    {
        $this->bindDn = $bindDn;
    }
    /**
    * Get address.
    *
    * @return address.
    */
    public function getAddress()
    {
       return $this->address;
    }
    /**
    * Set address.
    *
    * @param address the value to set.
    */
    public function setAddress($address)
    {
        $this->address = $address;
    }
    /**
    * Get port.
    *
    * @return port.
    */
    public function getPort()
    {
       return $this->port;
    }
    /**
    * Set port.
    *
    * @param port the value to set.
    */
    public function setPort($port)
    {
        $this->port = $port;
    }
    /**
    * Get connected.
    *
    * @return connected.
    */
    public function getConnected()
    {
       return $this->connected;
    }
    /**
    * Set connected.
    *
    * @param connected the value to set.
    */
    public function setConnected($connected)
    {
        $this->connected = $connected;
    }
    /**
    * Get resource.
    *
    * @return resource.
    */
    public function getResource()
    {
       return $this->resource;
    }
    /**
    * Set resource.
    *
    * @param resource the value to set.
    */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
    * Get baseGroup.
    *
    * @return baseGroup.
    */
    public function getBaseGroup()
    {
       return $this->baseGroup;
    }
    /**
    * Set baseGroup.
    *
    * @param baseGroup the value to set.
    */
    public function setBaseGroup($baseGroup)
    {
        $this->baseGroup = $baseGroup;
    }
    /**
    * Get groupFilter.
    *
    * @return groupFilter.
    */
    public function getGroupFilter()
    {
       return $this->groupFilter;
    }
    /**
    * Set groupFilter.
    *
    * @param groupFilter the value to set.
    */
    public function setGroupFilter($groupFilter)
    {
        $this->groupFilter = $groupFilter;
    }
    /**
    * Get userFilter.
    *
    * @return userFilter.
    */
    public function getUserFilter()
    {
       return $this->userFilter;
    }
    /**
    * Set userFilter.
    *
    * @param userFilter the value to set.
    */
    public function setUserFilter($userFilter)
    {
        $this->userFilter = $userFilter;
    }
    /**
    * Get roleMethod.
    *roro
    * @return roleMethod.
    */
    public function getRoleMethod()
    {
        return $this->roleMethod;
    }
    /**
    * Set roleMethod.
    *
    * @param roleMethod the value to set.
    */
    public function setRoleMethod($roleMethod)
    {
        $this->roleMethod = $roleMethod;
    }
    /**
    * Get roleAttributs.
    *roro
    * @return roleAttributs.
    */
    public function getRoleAttributs()
    {
        return $this->roleAttributs;
    }
    /**
    * Set roleAttributs.
    *
    * @param roleAttributs the value to set.
    */
    public function setRoleAttributs($roleAttributs)
    {
        $this->roleAttributs = $roleAttributs;
    }
}
