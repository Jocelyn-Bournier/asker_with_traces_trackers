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

namespace SimpleIT\ClaireExerciseBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;

/**
 * Class BaseRepository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class BaseRepository extends EntityRepository
{
    /**
     * Update a detached entity
     * Use with transactional annotation
     *
     * @param mixed $entity
     *
     * @return mixed
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException
     */
    public function update($entity)
    {
        if ($this->_em->contains($entity)) {
            $entityToUpdate = $entity;
        } else {
            $entityToUpdate = $this->findOneByEntity($entity);

            // Set only the modified fields
            $entityToUpdate = $this->updateModifiedField($entityToUpdate, $entity);
        }
        $this->_em->persist($entityToUpdate);

        return $entityToUpdate;
    }

    /**
     * Find an entity by field set
     * <ul>
     *  <li>identifiers</li>
     *  <li>uniques</li>
     * </ul>
     *
     * @param mixed $entityToFind
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function findOneByEntity($entityToFind)
    {
        $entity = null;
        $classMetadata = $this->_em->getClassMetadata(get_class($entityToFind));

        // Get the identifiers of the entity
        $identifiersNames = $classMetadata->getIdentifier();
        foreach ($identifiersNames as $identifierName) {
            $id = call_user_func(array($entityToFind, 'get' . $identifierName));
            if (isset($id)) {
                $ids[$identifierName] = $id;
            }
        }

        // If identifiers are not defined, get the unique fields
        if (!isset($ids)) {
            foreach ($classMetadata->getFieldNames() as $fieldName) {
                if ($classMetadata->isUniqueField($fieldName)) {
                    $field = call_user_func(array($entityToFind, 'get' . $fieldName));
                    if (isset($field)) {
                        $uniques[$fieldName] = $field;
                    }
                }
            }
            // if unique fields are not defined, throw exception
            if (!isset($uniques)) {
                throw new \InvalidArgumentException('Missing identifier or unique fields');
            } else {
                $entity = $this->findOneBy($uniques);
            }
        } else {
            $entity = $this->find($ids);
        }

        return $entity;
    }

    /**
     * Delete an entity
     * Use with transactional annotation
     *
     * @param mixed $entity
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException
     */
    public function delete($entity)
    {
        if ($this->_em->contains($entity)) {
            $entityToDelete = $entity;
        } else {

            $entityToDelete = $this->findOneByEntity($entity);
        }
        if (is_null($entityToDelete)) {
            throw new NonExistingObjectException();
        }

        $this->_em->remove($entityToDelete);
    }

    /**
     * Delete an Entity by id
     * Use with transactional annotation
     *
     * @param mixed $id
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException
     */
    public function deleteById($id)
    {
        $entity = $this->find($id);
        if (is_null($entity)) {
            throw new NonExistingObjectException();
        }
        $this->_em->remove($entity);
    }

    /**
     * Update only the modified fields and associations of an entity
     *
     * @param mixed $entityToUpdate Entity to update
     * @param mixed $entity         Entity
     * @param bool  $depth          True if associations of the entity has to be updated, false otherwise
     *
     * @return mixed
     */
    private function updateModifiedField($entityToUpdate, $entity, $depth = true)
    {
        $classMetadata = $this->_em->getClassMetadata(get_class($entity));
        foreach ($classMetadata->getFieldNames() as $field) {
            $fieldValue = call_user_func(array($entity, 'get' . $field));

            if (isset($fieldValue)) {
                $classMetadata->setFieldValue($entityToUpdate, $field, $fieldValue);
            }
        }
        // To prevent loading all the database
        if ($depth) {
            // Set only the modified associations
            foreach ($classMetadata->getAssociationNames() as $association) {
                $associatedEntity = call_user_func(array($entity, 'get' . $association));
                if (isset($associatedEntity)) {
                    $associatedEntityToUpdate = call_user_func(
                        array(
                            $entityToUpdate,
                            'get' . $association
                        )
                    );
                    if (!$associatedEntity instanceof ArrayCollection) {
                        $associatedEntityToUpdate = $this->updateModifiedField(
                            $associatedEntityToUpdate,
                            $associatedEntity,
                            false
                        );
                        call_user_func(
                            array($entityToUpdate, 'set' . $association),
                            $associatedEntityToUpdate
                        );
                    }
                }
            }
        }

        return $entityToUpdate;
    }
}
