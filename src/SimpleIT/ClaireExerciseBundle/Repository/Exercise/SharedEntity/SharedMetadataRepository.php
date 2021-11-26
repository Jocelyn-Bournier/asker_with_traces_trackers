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

namespace SimpleIT\ClaireExerciseBundle\Repository\Exercise\SharedEntity;

use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\Metadata;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\SharedEntity;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Collection\Sort;
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * SharedMetadata repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class SharedMetadataRepository extends BaseRepository
{
    const METADATA_TABLE = 'Name of the table';

    const ENTITY_ID_FIELD_NAME = 'Name of the field';

    const ENTITY_NAME = 'Name of the entity';

    /**
     * Find a model by id
     *
     * @param array $parameters
     *
     * @return Metadata
     * @throws NonExistingObjectException
     */
    public function find($parameters, $lockMode = null, $lockVersion = null)
    {
        $metadata = parent::find($parameters);
        if ($metadata === null) {
            throw new NonExistingObjectException();
        }

        return $metadata;
    }

    /**
     * Return all the metadata
     *
     * @param CollectionInformation $collectionInformation
     * @param SharedEntity          $entity
     *
     * @return array
     */
    public function findAllBy(
        $collectionInformation = null,
        $entity = null
    )
    {
        $queryBuilder = $this->createQueryBuilder('m');

        if (!is_null($entity)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'm.' . static::ENTITY_NAME,
                    $entity->getId()
                )
            );
        }

        // Handle Collection Information
        if (!is_null($collectionInformation)) {
            $sorts = $collectionInformation->getSorts();

            foreach ($sorts as $sort) {
                /** @var Sort $sort */
                switch ($sort->getProperty()) {
                    case static::ENTITY_NAME:
                        $queryBuilder->addOrderBy('m.' . static::ENTITY_NAME, $sort->getOrder());
                        break;
                    case 'key':
                        $queryBuilder->addOrderBy('m.key', $sort->getOrder());
                        break;
                }
            }
        } else {
            $queryBuilder->addOrderBy('m.' . static::ENTITY_NAME, 'ASC');
            $queryBuilder->addOrderBy('m.key', 'ASC');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Delete all the metadata for an entity
     *
     * @param SharedEntity $entity
     */
    public function deleteAllByEntity($entity)
    {
        if (count($entity->getMetadata()) > 0) {
            $qb = $this->createQueryBuilder('m');
            $qb->delete(get_class($entity->getMetadata()[0]), 'm');
            $qb->where($qb->expr()->eq('m.' . static::ENTITY_NAME, $entity->getId()));
            $qb->getQuery()->getResult();
        }
    }
}
