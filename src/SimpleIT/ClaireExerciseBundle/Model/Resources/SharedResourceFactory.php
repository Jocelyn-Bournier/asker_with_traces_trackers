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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use SimpleIT\ClaireExerciseBundle\Entity\DomainKnowledge\Knowledge;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\Metadata;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\SharedEntity;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException;
use SimpleIT\ClaireExerciseBundle\Serializer\Handler\AbstractClassForExerciseHandler;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SharableResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class SharedResourceFactory
{
    const KNOWLEDGE = 'knowledge';

    const EXERCISE_MODEL = 'exerciseModel';

    const RESOURCE = 'resource';

    /**
     * @param SharedResource $resource
     * @param SharedEntity   $entity
     */
    protected static function fill(&$resource, $entity)
    {
        $resource->setId($entity->getId());
        $resource->setType($entity->getType());
        $resource->setTitle($entity->getTitle());
        $resource->setAuthor($entity->getAuthor()->getId());
        $resource->setPublic($entity->getPublic());
        $resource->setArchived($entity->getArchived());
        $resource->setOwner($entity->getOwner()->getId());
        $resource->setDraft($entity->getDraft());
        $resource->setComplete($entity->getComplete());
        $resource->setCompleteError($entity->getCompleteError());

        // Parent and fork from
        if (!is_null($entity->getParent())) {
            $resource->setParent($entity->getParent()->getId());
        }
        if (!is_null($entity->getForkFrom())) {
            $resource->setForkFrom($entity->getForkFrom()->getId());
        }

        // metadata and keywords
        $metadataArray = array();
        $keywordArray = array();
        /** @var Metadata $md */
        foreach ($entity->getMetadata() as $md) {
            if ($md->getKey() === MetadataResource::MISC_METADATA_KEY) {
                $keywordArray = array_merge($keywordArray, explode(';', $md->getValue()));
            } else {
                $metadataArray[] = MetadataResourceFactory::createFromKeyValue(
                    $md->getKey(),
                    $md->getValue()
                );
            }
        }
        $resource->setMetadata($metadataArray);
        $resource->setKeywords($keywordArray);

        // content
        if ($entity->getContent() !== null) {
            $serializer = SerializerBuilder::create()
                ->addDefaultHandlers()
                ->configureHandlers(
                    function (HandlerRegistry $registry) {
                        $registry->registerSubscribingHandler(
                            new AbstractClassForExerciseHandler()
                        );
                    }
                )
                ->build();
            $content = $serializer->deserialize(
                $entity->getContent(),
                $resource->getClass(),
                'json'
            );
            $resource->setContent($content);
        }
    }

    /**
     * Create a resource from an entity and the type of the entity
     *
     * @param SharedEntity $entity
     * @param string       $type
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     * @return SharedResource
     */
    public static function createFromEntity($entity, $type)
    {

        switch ($type) {
            case self::EXERCISE_MODEL:
                /** @var ExerciseModel $entity */
                $resource = ExerciseModelResourceFactory::create($entity);
                break;
            case self::RESOURCE:
                /** @var \SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\ExerciseResource $entity */
                $resource = ResourceResourceFactory::create($entity);
                break;
            case self::KNOWLEDGE:
                /** @var Knowledge $entity */
                $resource = KnowledgeResourceFactory::create($entity);
                break;
            default:
                throw new InvalidTypeException('Unknown type:' . $type);
        }

        return $resource;
    }

    /**
     * Create a collection of resources from a collection of entities and the type of the entity
     *
     * @param array  $entities
     * @param string $type
     *
     * @return array
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     */
    public static function createFromEntityCollection(array $entities, $type)
    {
        switch ($type) {
            case self::EXERCISE_MODEL:
                $resources = ExerciseModelResourceFactory::createCollection($entities);
                break;
            case self::RESOURCE:
                $resources = ResourceResourceFactory::createCollection($entities);
                break;
            case self::KNOWLEDGE:
                $resources = KnowledgeResourceFactory::createCollection($entities);
                break;
            default:
                throw new InvalidTypeException('Unknown type:' . $type);
        }

        return $resources;
    }
}
