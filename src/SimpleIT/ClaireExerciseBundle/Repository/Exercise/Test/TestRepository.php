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

use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModel;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Collection\Sort;
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * Class TestRepository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestRepository extends BaseRepository
{
    /**
     * Find all tests
     *
     * @param CollectionInformation $collectionInformation
     * @param TestModel             $testModel
     *
     * @return array
     */
    public function findAllBy($collectionInformation = null, $testModel = null)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        // Handle Collection Information
        if (!is_null($testModel)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    't.testModel',
                    $testModel->getId()
                )
            );
        }
        if (!is_null($collectionInformation)) {
            $sorts = $collectionInformation->getSorts();

            foreach ($sorts as $sort) {
                /** @var Sort $sort */
                switch ($sort->getProperty()) {
                    case 'id':
                        $queryBuilder->addOrderBy('t.id', $sort->getOrder());
                        break;
                    default:
                        $queryBuilder->addOrderBy('t.id');
                        break;
                }
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Find a test by id
     *
     * @param mixed $testAttemptId
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
