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
 * Class TestModelRepository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestModelRepository extends BaseRepository
{
    /**
     * Find a test model by its id
     *
     * @param mixed $testModelId
     *
     * @return TestModel
     * @throws NonExistingObjectException
     */
    public function find($testModelId)
    {
        $resource = parent::find($testModelId);
        if ($resource === null) {
            throw new NonExistingObjectException();
        }

        return $resource;
    }

    /**
     * Find all
     *
     * @param CollectionInformation $collectionInformation
     *
     * @return array
     */
    public function findAllBy($collectionInformation = null)
    {
        $queryBuilder = $this->createQueryBuilder('tm');

        // Handle Collection Information
        if (!is_null($collectionInformation)) {
            $sorts = $collectionInformation->getSorts();

            foreach ($sorts as $sort) {
                /** @var Sort $sort */
                switch ($sort->getProperty()) {
                    case 'id':
                        $queryBuilder->addOrderBy('tm.id', $sort->getOrder());
                        break;
                    case 'title':
                        $queryBuilder->addOrderBy('t.title', $sort->getOrder());
                        break;
                    default:
                        $queryBuilder->addOrderBy('t.id');
                        break;
                }
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
