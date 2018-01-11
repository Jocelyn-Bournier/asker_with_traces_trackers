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

namespace SimpleIT\ClaireExerciseBundle\Entity;

use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Knowledge;
use SimpleIT\ClaireExerciseBundle\Model\Resources\KnowledgeResource;

/**
 * Class to manage the creation of Knowledge
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class KnowledgeFactory extends SharedEntityFactory
{
    /**
     * Create a new Knowledge object
     *
     * @param string $content Content
     *
     * @return Knowledge
     */
    public static function create($content = '')
    {
        $knowledge = new Knowledge();
        parent::initialize($knowledge, $content);

        return $knowledge;
    }

    /**
     * Create a knowledge entity from a knowledge resource and the author
     *
     * @param KnowledgeResource $knowledgeResource
     *
     * @return Knowledge
     */
    public static function createFromResource(
        KnowledgeResource $knowledgeResource
    )
    {
        $knowledge = new Knowledge();
        parent::fillFromResource($knowledge, $knowledgeResource, 'knowledge_storage');

        return $knowledge;
    }
}
