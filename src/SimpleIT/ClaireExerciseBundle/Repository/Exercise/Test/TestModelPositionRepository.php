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
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * Class TestModelPositionRepository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class TestModelPositionRepository extends BaseRepository
{
    /**
     * Delete all the positions for a test model
     *
     * @param TestModel $testModel
     */
    public function deleteAllPositions($testModel)
    {
        if (count($testModel->getTestModelPositions()) > 0) {
            $qb = $this->createQueryBuilder('tmp');
            $qb->delete(
                get_class($testModel->getTestModelPositions()[0]),
                'tmp'
            );
            $qb->where($qb->expr()->eq('tmp.testModel', $testModel->getId()));
            $qb->getQuery()->getResult();
        }
    }
}
