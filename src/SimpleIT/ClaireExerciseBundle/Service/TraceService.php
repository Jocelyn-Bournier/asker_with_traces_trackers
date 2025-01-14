<?php

namespace SimpleIT\ClaireExerciseBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use SimpleIT\ClaireExerciseBundle\Entity\Trace;

class TraceService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function saveTrace(int $user_id, string $type, \DateTime $dd, \DateTime $df, array $content, array $context)
    {
        $trace = new Trace($user_id, $type, $dd, $df, $content, $context);
        $this->em->persist($trace);
        $this->em->flush();

        return $trace;
    }
}