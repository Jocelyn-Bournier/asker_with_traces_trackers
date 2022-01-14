<?php

namespace CRT\ToolBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use CRT\ToolBundle\Entity\RequestStatus;
use CRT\ToolBundle\Definitions\Definitions;

class LoadRequestStatusData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $def = new Definitions;
        $statuses = array(
            $def->getWaiting(),
            $def->getNoAnswer(),
            $def->getConfirmed(),
            $def->getExpired(),
        );
        foreach($statuses as $label ){
            $status = new RequestStatus();
            $status->setLabel($label);
            $manager->persist($status);
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

