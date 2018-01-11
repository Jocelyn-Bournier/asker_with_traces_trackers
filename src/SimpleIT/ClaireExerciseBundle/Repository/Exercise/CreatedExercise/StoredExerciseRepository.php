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

use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestAttempt;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Collection\Sort;
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * StoredExercise repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class StoredExerciseRepository extends BaseRepository
{
    /**
     * Return all the stored exercises corresponding to an exercise model (if specified)
     *
     * @param CollectionInformation $collectionInformation
     * @param ExerciseModel         $exerciseModel
     *
     * @return array
     */
    public function findAllBy(
        $collectionInformation = null,
        $exerciseModel = null
    )
    {
        $queryBuilder = $this->createQueryBuilder('se');

        if (!is_null($exerciseModel)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'se.exerciseModel',
                    $exerciseModel->getId()
                )
            );
        }

        $queryBuilder->addOrderBy('se.id');

        // Handle Collection Information
        if (!is_null($collectionInformation)) {
            $filters = $collectionInformation->getFilters();
            foreach ($filters as $filter => $value) {
                switch ($filter) {
                    case 'exerciseModelId':
                        $queryBuilder->andWhere(
                            $queryBuilder->expr()->eq(
                                'se.exerciseModel',
                                $value
                            )
                        );
                        break;
                    default:
                        break;
                }
            }
            $sorts = $collectionInformation->getSorts();

            foreach ($sorts as $sort) {
                /** @var Sort $sort */
                switch ($sort->getProperty()) {
                    case 'title':
                        $queryBuilder->addOrderBy('se.title', $sort->getOrder());
                        break;
                    case 'id':
                        $queryBuilder->addOrderBy('se.id', $sort->getOrder());
                        break;
                }
            }
        }

        return $queryBuilder->getQuery()->getResult();

    }

    /**
     * Get all the exercises by test attempt Id
     *
     * @param TestAttempt $testAttempt
     *
     * @return array
     */
    public function findAllByTestAttempt(TestAttempt $testAttempt)
    {
        $queryBuilder = $this->createQueryBuilder('se');
        $queryBuilder->leftJoin('se.testPositions', 'tp');
        $queryBuilder->leftJoin('tp.test', 't');
        $queryBuilder->leftJoin('t.testAttempts', 'ta');

        $queryBuilder->where($queryBuilder->expr()->eq('ta.id', $testAttempt->getId()));
        $queryBuilder->orderBy('tp.position');

        return $queryBuilder->getQuery()->getResult();
    }


    function findByModelUser($model, $user)
    {
        return $this->createQueryBuilder('s')
	    ->select('s.id')
	    ->join('s.exerciseModel', 'em')
	    ->join('s.attempts','a')
	    ->where('a.user = :user')
	    ->andWhere('em.id = :model')
	    ->setParameter('user', $user)
	    ->setParameter('model',$model)
	    ->getQuery()
            ->getArrayResult();
    }
}
