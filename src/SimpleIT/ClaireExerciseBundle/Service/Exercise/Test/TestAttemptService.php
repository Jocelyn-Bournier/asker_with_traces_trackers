<?php
/*
 * This file is part of CLAIRE.
 *
 * CLAIRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CLAIRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CLAIRE. If not, see <http://www.gnu.org/licenses/>
 */

namespace SimpleIT\ClaireExerciseBundle\Service\Exercise\Test;

use SimpleIT\ClaireExerciseBundle\Entity\Test\TestAttempt;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestPosition;
use SimpleIT\ClaireExerciseBundle\Entity\TestAttemptFactory;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\Test\TestAttemptRepository;
use SimpleIT\ClaireExerciseBundle\Service\Exercise\CreatedExercise\AttemptService;
use SimpleIT\ClaireExerciseBundle\Service\TransactionalService;
use SimpleIT\ClaireExerciseBundle\Service\User\UserServiceInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service which manages the test attempts
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestAttemptService extends TransactionalService implements TestAttemptServiceInterface
{
    /**
     * @var TestAttemptRepository
     */
    private $testAttemptRepository;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @var AttemptService
     */
    private $attemptService;

    /**
     * @var  TestServiceInterface
     */
    private $testService;

    /**
     * Set testAttemptRepository
     *
     * @param $testAttemptRepository
     */
    public function setTestAttemptRepository($testAttemptRepository)
    {
        $this->testAttemptRepository = $testAttemptRepository;
    }

    /**
     * Set userService
     *
     * @param UserServiceInterface $userService
     */
    public function setUserService($userService)
    {
        $this->userService = $userService;
    }

    /**
     * Set attemptService
     *
     * @param AttemptService $attemptService
     */
    public function setAttemptService($attemptService)
    {
        $this->attemptService = $attemptService;
    }

    /**
     * Set testService
     *
     * @param TestServiceInterface $testService
     */
    public function setTestService($testService)
    {
        $this->testService = $testService;
    }

    /**
     * Find a test attempt by its id
     *
     * @param int $testAttemptId Test attempt Id
     * @param int $userId
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException
     * @return TestAttempt
     */
    public function get($testAttemptId, $userId = null)
    {
        $testAttempt = $this->testAttemptRepository->find($testAttemptId);
        if (is_null($testAttempt)) {
            throw new NonExistingObjectException();
        } elseif ($userId !== null && $testAttempt->getUser()->getId() !== $userId) {
            throw new AccessDeniedException();
        }

        return $testAttempt;
    }

    /**
     * Add a new test attempt to the database.
     *
     * @param int $testId
     * @param int $userId
     *
     * @return TestAttempt
     */
    public function add($testId, $userId)
    {
        $user = $this->userService->get($userId);
        $test = $this->testService->get($testId);
        $testAttempt = TestAttemptFactory::create($test, $user);

        /** @var TestAttempt $testAttempt */
        $this->em->persist($testAttempt);

        foreach ($test->getTestPositions() as $position) {
            /** @var TestPosition $position */
            $exerciseId = $position->getExercise()->getId();
            $this->attemptService->add(
                $exerciseId,
                $userId,
                $testAttempt->getId(),
                $position->getPosition()
            );
        }

        $this->em->flush();

        return $testAttempt;
    }

    /**
     * Get all the test attempts
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $userId
     * @param int                   $testId
     *
     * @return array
     */
    public function getAll(
        $collectionInformation = null,
        $userId = null,
        $testId = null
    )
    {
        $test = null;
        if (!is_null($testId)) {
            $test = $this->testService->get($testId);
        }

        return $this->testAttemptRepository->findAllBy(
            $collectionInformation,
            $userId,
            $test
        );
    }
}
