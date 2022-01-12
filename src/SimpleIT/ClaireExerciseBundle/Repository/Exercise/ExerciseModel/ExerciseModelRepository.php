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

namespace SimpleIT\ClaireExerciseBundle\Repository\Exercise\ExerciseModel;

use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\SharedEntity\SharedEntityRepository;

/**
 * ExerciseModel repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseModelRepository extends SharedEntityRepository
{
    /**
     * Find a model by id
     *
     * @param mixed $exerciseModelId
     *
     * @return ExerciseModel
     * @throws NonExistingObjectException
     */
    public function find($exerciseModelId, $lockMode = null, $lockVersion = null)
    {
        $exerciseModel = parent::find($exerciseModelId);
        if ($exerciseModel === null) {
            throw new NonExistingObjectException();
        }

        return $exerciseModel;
    }
    /**
     * Find the all
     *
     * @param $userId
     *
     * @return array
     */
    public function findAllByUser($userId)
    {
        $qb = $this->createQueryBuilder('em')
            ->select('em, e, at, an');

        $qb->leftJoin('em.exercises', 'e');
        $qb->leftJoin('e.attempts', 'at');
        $qb->leftJoin('at.answers', 'an');

        $qb->where($qb->expr()->eq('at.user', $userId));

        $qb->addOrderBy('em.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find the models attempted by the user, and the answers
     *
     * @param $userId
     *
     * @return array
     */
    public function findAllByUserWhoAttempted($userId)
    {
        $qb = $this->createQueryBuilder('em')
            ->select('em, e, at, an');

        $qb->leftJoin('em.exercises', 'e');
        $qb->leftJoin('e.attempts', 'at');
        $qb->leftJoin('at.answers', 'an');

        $qb->where($qb->expr()->eq('at.user', $userId));

        $qb->addOrderBy('em.id');

        return $qb->getQuery()->getResult();
        //$qb = $this->createQueryBuilder('em')
        //    ->select('em, e');

        //$qb->leftJoin('em.exercises', 'e');
        //$qb->join('e.attempts','at');
        //$qb->where($qb->expr()->eq('at.user', $userId));

        //$qb->addOrderBy('em.id');

        //return $qb->getQuery()->getResult();
    }

    /**
     * Find the models attempted by the user, and the answers
     *
     * @param $userId
     * @param $modelId
     *
     * @return array
     */
    public function findAllByUserWhoAttemptedByModel($userId, $modelId)
    {
        $qb = $this->createQueryBuilder('em')
            ->select('em, e, at, an');

        $qb->leftJoin('em.exercises', 'e');
        $qb->leftJoin('e.attempts', 'at');
        $qb->leftJoin('at.answers', 'an');

        $qb->andWhere($qb->expr()->eq('em.id', $modelId));
        $qb->andWhere($qb->expr()->eq('at.user', $userId));

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get the join that reduce the number of requests.
     *
     * @return array
     */
    protected function getLeftJoins()
    {
        return array(
            "rr" => "entity.requiredExerciseResources",
            "rk" => "entity.requiredKnowledges"
        );
    }
}
