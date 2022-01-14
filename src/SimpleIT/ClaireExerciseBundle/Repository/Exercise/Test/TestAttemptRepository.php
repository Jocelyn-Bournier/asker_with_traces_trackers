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

namespace SimpleIT\ClaireExerciseBundle\Repository\Exercise\Test;

use SimpleIT\ClaireExerciseBundle\Entity\Test\Test;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Collection\Sort;
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * TestAttempt Repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestAttemptRepository extends BaseRepository
{
    /**
     * Find all test attempts
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $userId
     * @param Test                  $test
     *
     * @return array
     */
    public function findAllBy(
        $collectionInformation = null,
        $userId = null,
        $test = null
    )
    {
        $queryBuilder = $this->createQueryBuilder('ta');

        if (!is_null($userId)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'ta.user',
                    $userId
                )
            );
        }

        if (!is_null($test)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'ta.test',
                    $test->getId()
                )
            );
        }

        // Handle Collection Information
        if (!is_null($collectionInformation)) {
            $sorts = $collectionInformation->getSorts();

            foreach ($sorts as $sort) {
                /** @var Sort $sort */
                switch ($sort->getProperty()) {
                    case 'testId':
                        $queryBuilder->addOrderBy('ta.test', $sort->getOrder());
                        break;
                    case 'userId':
                        $queryBuilder->addOrderBy('ta.user', $sort->getOrder());
                        break;
                    case 'id':
                        $queryBuilder->addOrderBy('ta.id', $sort->getOrder());
                        break;
                    default:
                        $queryBuilder->addOrderBy('ta.id');
                        break;
                }
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Find a test attempt by id
     *
     * @param int $testAttemptId
     *
     * @return object
     * @throws NonExistingObjectException
     */
    public function find($testAttemptId, $lockMode = null, $lockVersion = null)
    {
        $test = parent::find($testAttemptId);
        if ($test === null) {
            throw new NonExistingObjectException();
        }

        return $test;
    }
}
