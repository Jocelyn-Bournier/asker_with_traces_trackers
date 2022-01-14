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

use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Metadata as KnowledgeMetadata;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\Metadata as ModelMetadata;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\Metadata as ResourceMetadata;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\Metadata;
use SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource;

/**
 * Class SharedEntityMetadataFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class SharedEntityMetadataFactory
{

    const EXERCISE_MODEL = 'exerciseModel';

    const RESOURCE = 'resource';

    const KNOWLEDGE = 'knowledge';

    /**
     * Create a metadata
     *
     * @param string $entityType
     * @param string $key   Key
     * @param string $value Value
     *
     * @return Metadata
     */
    static public function create($entityType, $key = null, $value = null)
    {
        $metadata = self::newFromEntityType($entityType);
        $metadata->setKey($key);
        $metadata->setValue($value);

        return $metadata;
    }

    /**
     * Create a metadata form resource
     *
     * @param string           $entityType
     * @param MetadataResource $metadataResource Metadata resource
     *
     * @return Metadata
     */
    static public function createFromResource($entityType, MetadataResource $metadataResource)
    {
        $metadata = self::newFromEntityType($entityType);
        $metadata->setKey($metadataResource->getKey());
        $metadata->setvalue($metadataResource->getValue());

        return $metadata;
    }

    /**
     * Create a metadata from type
     *
     * @param $entityType
     *
     * @return Metadata
     * @throws \LogicException
     */
    static private function newFromEntityType($entityType)
    {
        switch ($entityType) {
            case self::EXERCISE_MODEL:
                return new ModelMetadata();
            case self::RESOURCE:
                return new ResourceMetadata();
            case self::KNOWLEDGE:
                return new KnowledgeMetadata();
            default:
                throw new \LogicException('Invalid type of entity:' . $entityType);
        }
    }
}
