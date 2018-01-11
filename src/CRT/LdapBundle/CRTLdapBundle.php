<?php

namespace CRT\LdapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use CRT\LdapBundle\Security\Factory\LdapAuthenticationFactory;

class CRTLdapBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory( new LdapAuthenticationFactory());
    }
}
