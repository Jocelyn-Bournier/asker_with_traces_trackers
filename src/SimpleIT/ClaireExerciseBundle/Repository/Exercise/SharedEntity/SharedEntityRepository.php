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

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\QueryBuilder;
use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\SharedEntity;
use SimpleIT\ClaireExerciseBundle\Exception\EntityAlreadyExistsException;
use SimpleIT\ClaireExerciseBundle\Exception\FilterException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Collection\Sort;
use SimpleIT\ClaireExerciseBundle\Repository\BaseRepository;

/**
 * Abstract SharedBaseRepository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class SharedEntityRepository extends BaseRepository
{
    /**
     * Find a list of entities according to a type an to metadata contained in a
     * collection information
     *
     * @param CollectionInformation $collectionInformation
     * @param int                   $authenticatedUserId
     * @param AskerUser                  $owner
     * @param AskerUser                  $author
     * @param SharedEntity          $parent
     * @param SharedEntity          $forkFrom
     * @param boolean               $isRoot
     * @param boolean               $isPointer
     * @param boolean               $ignoreArchived
     * @param int                   $publicExceptUser Get the public entities that are not owned by this user
     * @param boolean               $complete
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\FilterException
     * @return array
     */
    public function findAll(
        $collectionInformation = null,
        $authenticatedUserId = null,
        $owner = null,
        $author = null,
        $parent = null,
        $forkFrom = null,
        $isRoot = null,
        $isPointer = null,
        $ignoreArchived = false,
        $publicExceptUser = null,
        $complete = null
    )
    {
        $metadata = array();
        $keywords = array();

        // build select
        $lj = $this->getLeftJoins();
        $select = 'entity, md';
        foreach (array_keys($lj) as $alias) {
            $select .= ', ' . $alias;
        }

        $qb = $this->createQueryBuilder('entity')->select($select);
        $qb->leftJoin('entity.metadata', 'md');

        // add other joins
        foreach ($lj as $alias => $table) {
            $qb->leftJoin($table, $alias);
        }

        if (!is_null($authenticatedUserId)) {
            $qb = $this->qbAuthenticatedUser($qb, $authenticatedUserId);
        }

        if (!is_null($owner)) {
            $qb = $this->qbOwner($qb, $owner->getId());
        }

        if (!is_null($author)) {
            $qb = $this->qbAuthor($qb, $author->getId());
        }

        if (!is_null($parent)) {
            $qb = $this->qbParent($qb, $parent->getId());
        }

        if (!is_null($forkFrom)) {
            $qb = $this->qbForkFrom($qb, $forkFrom->getId());
        }

        if ($ignoreArchived) {
            $qb = $this->qbNotArchived($qb);
        }

        if ($isPointer !== null) {
            $qb = $this->qbIsPointer($qb, $isPointer);
        }

        if ($isRoot === true) {
            $qb = $this->qbNotForkFrom($qb);
        }

        if (!is_null($publicExceptUser)) {
            $qb = $this->qbPublicExceptUser($qb, $publicExceptUser);
        }

        if (!is_null($complete)) {
            $qb = $this->qbPublicExceptUser($qb, $publicExceptUser);
        }

        if ($collectionInformation !== null) {

            $filters = $collectionInformation->getFilters();
            foreach ($filters as $filter => $value) {
                switch ($filter) {
                    case ('author'):
                        $qb = $this->qbAuthor($qb, $value);
                        break;
                    case ('owner'):
                        $qb = $this->qbOwner($qb, $value);
                        break;
                    case ('fork-from'):
                        $qb = $this->qbForkFrom($qb, $value);
                        break;
                    case ('parent'):
                        $qb = $this->qbParent($qb, $value);
                        break;
                    case ('ignore-archived'):
                        $qb = $this->qbNotArchived($qb);
                        break;
                    case ('is-pointer'):
                        $qb = $this->qbIsPointer($qb, $value);
                        break;
                    case ('is-root'):
                        if ($value === 'true') {
                            $qb = $this->qbNotForkFrom($qb);
                        }
                        break;
                    case ('public'):
                        $qb = $this->qbPublic($qb, $value);
                        break;
                    case ('complete'):
                        if ($value !== "true" && $value !== "false") {
                            throw new FilterException('complete filter must be true or false');
                        }
                        $qb = $this->qbComplete($qb, $value);
                        break;
                    case ('public-except-user'):
                        $qb = $this->qbPublicExceptUser($qb, $value);
                        break;
                    case ('type'):
                        if (is_array($value)) {
                            $qpType = '';
                            foreach ($value as $val) {
                                if ($qpType !== '') {
                                    $qpType = $qb->expr()->orX(
                                        $qpType,
                                        $qb->expr()->eq('entity.type', "'" . $val . "'")
                                    );
                                } else {
                                    $qpType = $qb->expr()->eq('entity.type', "'" . $val . "'");
                                }
                            }
                            $qb->andWhere($qpType);
                        } else {
                            $qb->andWhere($qb->expr()->eq('entity.type', "'" . $value . "'"));
                        }
                        break;
                    case ('metadata'):
                        $metadata = $this->metadataToArray($value);
                        break;
                    case ('keywords'):
                        $keywords = $this->keywordsToArray($value);
                        break;
                    case ('draft'):
                        if ($value !== "true" && $value !== "false") {
                            throw new FilterException('draft filter must be true or false');
                        }
                        $qb->andWhere($qb->expr()->eq('entity.draft', "'" . $value . "'"));
                        break;
                }
            }

            // Metadata
            $i = 0;
            foreach ($metadata as $metaKey => $value) {
                $alias = 'm' . $i;
                $qb->leftJoin('entity.metadata', $alias);

                $qb->andWhere(
                    $qb->expr()->andX(
                        $qb->expr()->eq($alias . '.key', "'" . $metaKey . "'"),
                        $qb->expr()->eq($alias . '.value', "'" . $value . "'")
                    )
                );

                $i++;
            }

            // Misc keywords
            foreach ($keywords as $keyword) {
                $alias = 'm' . $i;
                $qb->leftJoin('entity.metadata', $alias);

                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq($alias . '.key', "'" . $keyword . "'"),
                        $qb->expr()->like($alias . '.value', "'%" . $keyword . "%'")
                    )
                );

                $i++;
            }

            // sort
            $sorts = $collectionInformation->getSorts();
            if (!empty($sorts)) {
                foreach ($sorts as $sort) {
                    /** @var Sort $sort */
                    switch ($sort->getProperty()) {
                        case 'title':
                            $qb->addOrderBy('entity.title', $sort->getOrder());
                            break;
                        case 'id':
                            $qb->addOrderBy('entity.id', $sort->getOrder());
                            break;
                        case 'type':
                            $qb->addOrderBy('entity.type', $sort->getOrder());
                            break;
                        case 'author':
                            $qb->addOrderBy('entity.author', $sort->getOrder());
                            break;
                    }
                }
            } else {
                $qb->orderBy('entity.public');
                $qb->addOrderBy('entity.id');
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Create the query part to filter by owner
     *
     * @param QueryBuilder $qb
     * @param int          $userId
     *
     * @return QueryBuilder
     */
    private function qbAuthenticatedUser(QueryBuilder $qb, $userId)
    {
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->eq('entity.owner', $userId),
                $qb->expr()->eq('entity.public', 'true')
            )
        );

        return $qb;
    }

    /**
     * Create the query part to filter by owner
     *
     * @param QueryBuilder $qb
     * @param int          $ownerId
     *
     * @return QueryBuilder
     */
    private function qbOwner(QueryBuilder $qb, $ownerId)
    {
        $qb->andWhere($qb->expr()->eq('entity.owner', $ownerId));

        return $qb;
    }

    /**
     * Create the query part to filter by author
     *
     * @param QueryBuilder $qb
     * @param int          $authorId
     *
     * @return QueryBuilder
     */
    private function qbAuthor(QueryBuilder $qb, $authorId)
    {
        $qb->andWhere($qb->expr()->eq('entity.author', $authorId));

        return $qb;
    }

    /**
     * Create the query part to filter by public
     *
     * @param QueryBuilder $qb
     * @param string|bool  $public
     *
     * @return QueryBuilder
     */
    private function qbPublic(QueryBuilder $qb, $public)
    {
        if ($public === 'true' || $public === true) {
            $public = 'true';
        } else {
            $public = 'false';
        }

        $qb->andWhere(
            $qb->expr()->eq(
                'entity.public',
                $public
            )
        );

        return $qb;
    }

    /**
     * Create the query part to filter by parent
     *
     * @param QueryBuilder $qb
     * @param int          $parentId
     *
     * @return QueryBuilder
     */
    private function qbParent(QueryBuilder $qb, $parentId)
    {
        $qb->andWhere($qb->expr()->eq('entity.parent', $parentId));

        return $qb;
    }

    /**
     * Create the query part to filter by fork from
     *
     * @param QueryBuilder $qb
     * @param int          $forkFromId
     *
     * @return QueryBuilder
     */
    private function qbForkFrom(QueryBuilder $qb, $forkFromId)
    {
        $qb->andWhere($qb->expr()->eq('entity.forkFrom', $forkFromId));

        return $qb;
    }

    /**
     * Create the query part to filter by fork from
     *
     * @param QueryBuilder $qb
     * @param string|bool  $complete
     *
     * @return QueryBuilder
     */
    private function qbComplete(QueryBuilder $qb, $complete)
    {
        if ($complete === true || $complete === 'true') {
            $qb->andWhere($qb->expr()->eq('entity.complete', 'true'));
        } else {
            $qb->andWhere($qb->expr()->eq('entity.complete', 'false'));
        }

        return $qb;
    }

    /**
     * Create the query part to get the public resources owned by other users
     *
     * @param QueryBuilder $qb
     * @param int          $value
     *
     * @return QueryBuilder
     * @throws FilterException
     */
    private function qbPublicExceptUser(QueryBuilder $qb, $value)
    {
        if (!is_numeric($value)) {
            throw new FilterException('public-except-user filter must be numeric');
        }
        $qb->andWhere($qb->expr()->neq('entity.owner', $value));
        $qb->andWhere($qb->expr()->eq('entity.public', 'true'));

        return $qb;
    }

    /**
     * Create the query part depending on the 'is pointer' parameter
     *
     * @param QueryBuilder $qb
     * @param bool|string  $isPointer
     *
     * @return QueryBuilder
     */
    private function qbIsPointer(QueryBuilder $qb, $isPointer)
    {
        if ($isPointer === true || $isPointer === 'true') {
            $qb->andWhere($qb->expr()->isNotNull('entity.parent'));
        } elseif ($isPointer === false || $isPointer === 'false') {
            $qb->andWhere($qb->expr()->isNotNull('entity.content'));
        }

        return $qb;
    }

    /**
     * Create the query part to get the public resources that are not archived
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     * @throws FilterException
     */
    private function qbNotArchived(QueryBuilder $qb)
    {
        $qb->andWhere($qb->expr()->eq('entity.archived', 'false'));

        return $qb;
    }

    /**
     * Create the query part to get the resources that are root (no fork)
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     * @throws FilterException
     */
    private function qbNotForkFrom(QueryBuilder $qb)
    {
        $qb->andWhere($qb->expr()->isNull('entity.forkFrom'));

        return $qb;
    }

    /**
     * Convert the content of keywords filter into an array
     *
     * @param string|array $keywords
     *
     * @return array
     */
    private function keywordsToArray($keywords)
    {
        if (is_array($keywords)) {
            return $keywords;
        } else {
            return array($keywords);
        }
    }

    /**
     * Converts the content of the metadata filter into a key => value array
     *
     * @param string|array $metadata
     *
     * @return array
     */
    private function metadataToArray($metadata)
    {
        $metadataArray = array();
        if (is_array($metadata)) {
            foreach ($metadata as $md) {
                $explode = explode(':', $md);
                $metadataArray[$explode[0]] = $explode[1];
            }
        } else {
            $explode = explode(':', $metadata);
            $metadataArray[$explode[0]] = $explode[1];
        }

        return $metadataArray;
    }

    /**
     * Add a required resource to an entity
     *
     * @param int          $entityId
     * @param SharedEntity $required
     * @param string       $table
     * @param string       $reqEntityName
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\EntityAlreadyExistsException
     */
    protected function addRequired($entityId, SharedEntity $required, $table, $reqEntityName)
    {
        $sql = 'INSERT INTO ' . $table . ' VALUES (:entityId,:requiredId)';

        $connection = $this->_em->getConnection();
        try {
            $connection->executeQuery(
                $sql,
                array(
                    'entityId'   => $entityId,
                    'requiredId' => $required->getId(),
                )
            );
        } catch (DBALException $e) {
            throw new EntityAlreadyExistsException("Required " . $reqEntityName);
        }
    }

    /**
     * Find an entity if it is owned by the user
     *
     * @param int  $entityId
     * @param AskerUser $owner
     *
     * @return SharedEntity
     * @throws NonExistingObjectException
     */
    public function findByIdAndOwner($entityId, AskerUser $owner)
    {
        $queryBuilder = $this->createQueryBuilder('e');

        $queryBuilder->where($queryBuilder->expr()->eq('e.owner', $owner->getId()));
        $queryBuilder->andWhere($queryBuilder->expr()->eq('e.id', $entityId));

        $result = $queryBuilder->getQuery()->getResult();

        if (empty($result)) {
            throw new NonExistingObjectException('Unable to find entity ' . $entityId .
            ' for owner ' . $owner->getId());
        } else {
            return $result[0];
        }
    }

    /**
     * Find an entity if it is owned by the user
     *
     * @param int $forkFromId
     * @param int $ownerId
     *
     * @return SharedEntity
     * @throws NonExistingObjectException
     */
    public function findByForkFromAndOwner($forkFromId, $ownerId)
    {
        $queryBuilder = $this->createQueryBuilder('e');

        $queryBuilder->where($queryBuilder->expr()->eq('e.owner', $ownerId));
        $queryBuilder->andWhere($queryBuilder->expr()->eq('e.forkFrom', $forkFromId));

        $result = $queryBuilder->getQuery()->getResult();

        if (empty($result)) {
            throw new NonExistingObjectException('Unable to find entity for fork' . $forkFromId .
            ' and for owner ' . $ownerId);
        } else {
            return $result[0];
        }
    }

    /**
     * Get the join that reduce the number of requests.
     *
     * @return array
     */
    abstract protected function getLeftJoins();
}
