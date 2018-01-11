<?php

namespace CRT\LdapBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;


class LdapAuthenticationFactory extends FormLoginFactory{

    public function getKey()
    {
        return 'ldap_login';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'ldap.security.check.provider.'.$id;
        
        $container
            ->setDefinition($provider, new DefinitionDecorator('ldap.security.check.provider'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(2, $id)
        ;
        return $provider;
    }
}
