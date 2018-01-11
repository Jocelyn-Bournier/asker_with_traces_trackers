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

namespace SimpleIT\ClaireExerciseBundle\Repository\Exercise\CreatedExercise;

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Attempt;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\StoredExercise;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestAttempt;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Collection\Sort;
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * Attempt Repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AttemptRepository extends BaseRepository
{
    /**
     * Find an attempt by id
     *
     * @param int $itemId
     *
     * @return Attempt
     * @throws NonExistingObjectException
     */
    public function find($itemId)
    {
        $item = parent::find($itemId);
        if ($item === null) {
            throw new NonExistingObjectException();
        }

        return $item;
    }

    /**
     * Return all the attempts
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $userId
     * @param StoredExercise        $exercise
     * @param TestAttempt           $testAttempt
     *
     * @return array
     */
    public function findAllBy(
        $collectionInformation = null,
        $userId = null,
        $exercise = null,
        $testAttempt = null
    )
    {
        $queryBuilder = $this->createQueryBuilder('a');

        if (!is_null($exercise)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'a.exercise',
                    $exercise->getId()
                )
            );
        }

        if (!is_null($testAttempt)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'a.testAttempt',
                    $testAttempt->getId()
                )
            );
        }

        if (!is_null($userId)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'a.user',
                    $userId
                )
            );
        }

        // Handle Collection Information
        if (!is_null($collectionInformation)) {
            $filters = $collectionInformation->getFilters();
            foreach ($filters as $filter => $value) {
                switch ($filter) {
                    case 'userId':
                        $queryBuilder->andWhere(
                            $queryBuilder->expr()->eq(
                                'a.user',
                                $value
                            )
                        );
                        break;
                    case 'testAttemptId':
                        $queryBuilder->andWhere(
                            $queryBuilder->expr()->eq(
                                'a.testAttempt',
                                $value
                            )
                        );
                        break;
                    default:
                        break;
                }
            }
            $sorts = $collectionInformation->getSorts();

            if (count($sorts) > 0) {
                foreach ($sorts as $sort) {
                    /** @var Sort $sort */
                    switch ($sort->getProperty()) {
                        case 'userId':
                            $queryBuilder->addOrderBy('a.user', $sort->getOrder());
                            break;
                        case 'testAttemptId':
                            $queryBuilder->addOrderBy('a.testAttempt', $sort->getOrder());
                            break;
                        case 'exerciseId':
                            $queryBuilder->addOrderBy('a.exercise', $sort->getOrder());
                            break;
                        case 'id':
                            $queryBuilder->addOrderBy('a.id', $sort->getOrder());
                            break;
                    }
                }
            } else {
                if (!is_null($testAttempt)) {
                    $queryBuilder->addOrderBy('a.position');
                } else {
                    $queryBuilder->addOrderBy('a.id');
                }
            }
        } else {
            $queryBuilder->addOrderBy('a.id');
        }

        return $queryBuilder->getQuery()->getResult();
    }
    function findByExerciseUser($exercise, $user)
    {
        return $this->createQueryBuilder('a')
            ->join('a.exercise', 'e')
            ->where('a.user = :user')
            ->andWhere('e.id = :exercise')
            ->setParameter('user', $user)
            ->setParameter('exercise',$exercise)
            ->getQuery()
            ->getResult();
    }
    function uniqueUsersByModel($model)
    {
        return $this->createQueryBuilder('a')
            ->select('count(distinct a.user) as total')
            ->join('a.exercise', 'e')
            ->where('e.exerciseModel = :model')
            ->setParameter('model',$model)
            ->getQuery()
            ->getResult();
    }


    function averageAttemptByModel($model)
    {
        //Native SQL because derived table doesnt work with Doctrine
        $sql = "
                SELECT AVG(total) as avg
                FROM(
                    SELECT count(*) AS total, user_id
                    FROM claire_exercise_attempt a
                    JOIN claire_exercise_stored_exercise s
                    ON s.id = a.exercise_id
                    WHERE exercise_model_id = :model
                    GROUP BY user_id
                ) d
                "
        ;
        $conn = $this->getEntityManager()
            ->getConnection()
        ;
        $stmt = $conn
            ->prepare($sql)
        ;
        $stmt->execute(array('model' => $model));
        return $stmt->fetchAll();
    }
}
