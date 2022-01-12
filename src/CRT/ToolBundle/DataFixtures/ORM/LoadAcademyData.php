<?php

namespace CRT\ToolBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use CRT\ToolBundle\Entity\Academy;

class LoadAcademyData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $academies = array(
            "education" => "Administration Centrale",
            "ac-aix-marseille" => "Aix-Marseille",
            "ac-amiens" => "Amiens",
            "siec" => "Arcueil",
            "ac-besancon" => "Besançon",
            "ac-bordeaux" => "Bordeaux",
            "ac-caen" => "Caen",
            "ac-clermont" => "Clermont-Ferrand",
            "ac-corse" => "Corse",
            "ac-creteil" => "Créteil",
            "ac-dijon" => "Dijon",
            "ac-grenoble" => "Grenoble",
            "ac-guadeloupe" => "Guadeloupe",
            "ac-guyane" => "Guyane",
            "ac-lille" => "Lille",
            "ac-limoges" => "Limoges",
            "ac-lyon" => "Lyon",
            "ac-martinique" => "Martinique",
            "ac-mayote" => "Mayotte",
            "ac-montpellier" => "Montpellier",
            "ac-nancy-metz" => "Nancy-Metz",
            "ac-nantes" => "Nantes",
            "ac-nice" => "Nice",
            "ac-noumea" => "Nouvelle-Calédonie",
            "ac-orleans-tours" => "Orleans-Tours",
            "ac-paris" => "Paris",
            "ac-poitiers" => "Poitiers",
            "ac-polynesie" => "Polynesie",
            "ac-reims" => "Reims",
            "ac-rennes" => "Rennes",
            "ac-reunion" => "Réunion",
            "ac-rouen" => "Rouen",
            "ac-strasbourg" => "Strasbourg",
            "ac-toulouse" => "Toulouse",
            "ac-versailles" => "Versailles",
            "ac-wf" => "Wallis et Futuna",
            "Prestataire" => "Prestataire hébergé en dehors d'une entité"
        );
        foreach($academies as $ldapName => $label ){
            $academy  = new Academy();
            $academy->setLabel($label);
            $academy->setLdapName($ldapName);
            $manager->persist($academy);
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

