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

namespace SimpleIT\ClaireExerciseBundle\Repository\Exercise\DomainKnowledge;

use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Knowledge;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\SharedEntity\SharedEntityRepository;

/**
 * Knowledge repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class KnowledgeRepository extends SharedEntityRepository
{
    /**
     * Find a knowledge by id
     *
     * @param mixed $knowledgeId
     *
     * @return Knowledge
     * @throws NonExistingObjectException
     */
    public function find($knowledgeId, $lockMode = null, $lockVersion = null)
    {
        $knowledge = parent::find($knowledgeId);
        if ($knowledge === null) {
            throw new NonExistingObjectException();
        }

        return $knowledge;
    }

    /**
     * Get the join that reduce the number of requests.
     *
     * @return array
     */
    protected function getLeftJoins()
    {
        return array(
            "rk" => "entity.requiredKnowledges"
        );
    }
}
