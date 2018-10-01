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

use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Answer;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Attempt;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * Answer repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AnswerRepository extends BaseRepository
{
    /**
     * Find an answer by id
     *
     * @param int $itemId
     *
     * @return Answer
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
     * Get all the answers. An item can be specified.
     *
     * @param Item    $item
     * @param Attempt $attempt
     *
     * @return array
     */
    public function findAllBy($item = null, $attempt = null)
    {
        $queryBuilder = $this->createQueryBuilder('a');

        if (!is_null($item)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'a.item',
                    $item->getId()
                )
            );
        }

        if (!is_null($attempt)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'a.attempt',
                    $attempt->getId()
                )
            );
        }

        $queryBuilder->add('orderBy', 'a.id', true);

        return $queryBuilder->getQuery()->getResult();
    }
    function uniqueUsersByModel($model,$view, $ids)
    {
        $qb = $this->createQueryBuilder('an')
            ->select('count(distinct a.user) as total')
            ->join('an.attempt', 'a')
            ->join('a.exercise', 'e')
            ->where('e.exerciseModel = :model')
            ->andWhere('a.user in (:ids)')
        ;
        if ($view){
            $qb
                ->andWhere('a.createdAt > :start')
                ->andWhere('a.createdAt < :end')
                ->setParameter('start', $view->getStartDate())
                ->setParameter('end', $view->getEndDate())
            ;
        }
        return $qb
            ->setParameter('model',$model)
            ->setParameter('ids',$ids)
            ->getQuery()
            ->getResult();
    }

    function averageMarkByModel($model,$view,$ids)
    {
        $sql = "
            select avg(mark) as avg
            from claire_exercise_answer an
            join claire_exercise_attempt at
            on an.attempt_id = at.id
            join claire_exercise_stored_exercise st
            on at.exercise_id = st.id
            where st.exercise_model_id = :model
	    AND an.created_at > :start
	    AND an.created_at < :end
        ";
        if (!empty($ids)){
            $sql .="
                    AND user_id in (".implode(',',$ids).")"
            ;
        }
        $conn = $this->getEntityManager()
            ->getConnection()
        ;
        $stmt = $conn
            ->prepare($sql)
        ;
        $stmt->execute(
		array(
			'model' => $model,
			'start' => $view->getStartDate()->format('Y-m-d'),
			'end' => $view->getEndDate()->format('Y-m-d')
		)
	);
        return $stmt->fetchAll();
    }

    function distributionMarkByModel($model,$view, $ids)
    {
    	$sql = "
		SELECT count(*) as total,
	        SUM(CASE WHEN an.mark >= 80 THEN 1 ELSE 0 END) top,
	        SUM(CASE WHEN an.mark < 80 AND an.mark >= 60 THEN 1 ELSE 0 END) midtop,
	        SUM(CASE WHEN an.mark < 60 AND an.mark >= 40 THEN 1 ELSE 0 END) mid,
	        SUM(CASE WHEN an.mark < 40 AND an.mark >= 20 THEN 1 ELSE 0 END) midbot,
	        SUM(CASE WHEN an.mark < 20 THEN 1 ELSE 0 END) bot
               	FROM claire_exercise_answer an
               	JOIN claire_exercise_attempt at
               	ON an.attempt_id = at.id
               	JOIN claire_exercise_stored_exercise st
               	ON at.exercise_id = st.id
             	WHERE st.exercise_model_id = :model
		    AND an.created_at > :start
		    AND an.created_at < :end
	";
        if (!empty($ids)){
            $sql .="
                    AND user_id in (".implode(',',$ids).")"
            ;
        }
        $conn = $this->getEntityManager()
            ->getConnection()
        ;
        $stmt = $conn
            ->prepare($sql)
        ;
        $stmt->execute(
		array(
			'model' => $model,
			'start' => $view->getStartDate()->format('Y-m-d'),
			'end' => $view->getEndDate()->format('Y-m-d')
		)
	);
        return $stmt->fetchAll();
    }

    function averageAnswerByModel($model,$view, $ids)
    {
        //Native SQL because derived table doesnt work with Doctrine
        // distinct because attempt can have many answers
        $sql = "
                SELECT AVG(total) as avg
                FROM(
                    SELECT count(distinct attempt_id) AS total, user_id
                    FROM claire_exercise_answer an
                    JOIN claire_exercise_attempt a
                    ON  an.attempt_id = a.id
                    JOIN claire_exercise_stored_exercise s
                    ON s.id = a.exercise_id
                    WHERE exercise_model_id = :model
		    AND an.created_at > :start
		    AND an.created_at < :end
        ";
        if (!empty($ids)){
            $sql .="
                    AND user_id in (".implode(',',$ids).")"
            ;
        }
        $sql .="
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
        $stmt->execute(
		array(
			'model' => $model,
			'start' => $view->getStartDate()->format('Y-m-d'),
			'end' => $view->getEndDate()->format('Y-m-d')
		)
	);
        return $stmt->fetchAll();
    }
}
