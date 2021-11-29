<?php

namespace CRT\ToolBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use CRT\ToolBundle\Entity\Corporate;

class LoadCorporateData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $corporates = array(
            'CES_CHOREGIE' => 'CES CHOREGIE',
            'CES_DECISIONNEL' => 'CES DECISIONNEL',
            'CES_FOAD' => 'CES FOAD',
            'CES_GOSPEL' => 'CES GOSPEL',
            'CES_SIRHEN' => 'CES SIRHEN',
            'CNS_DIR' => 'CNS DIR',
            'CNS_UC' => 'CNS UC',
            'CRT_ECD' => 'CRT ECD',
            'CRT_FIM' => 'CRT FIM',
            'CRT_HEB' => 'CRT HEB',
            'CRT_ITIL' => 'CRT ITIL',
            'CRT_RSO' => 'CRT RSO',
            'CRT_SUP' => 'CRT SUP',
            'ITP' => 'ITP',
            'SIRHEN_INTEGRATION' => 'SIRHEN INTEGRATION',
        );
        foreach($corporates as $ldapName => $label ){
            $corporate = new Corporate();
            $corporate->setLabel($label);
            $corporate->setLdapName($ldapName);
            $manager->persist($corporate);
        }
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 0; // l'ordre dans lequel les fichiers sont chargés
    }
}

