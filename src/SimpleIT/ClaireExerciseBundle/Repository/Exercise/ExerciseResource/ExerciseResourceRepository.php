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

namespace SimpleIT\ClaireExerciseBundle\Repository\Exercise\ExerciseResource;

use SimpleIT\ClaireExerciseBundle\Entity\AskerUser;
use Doctrine\ORM\QueryBuilder;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\ExerciseResource;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\MetadataConstraint;
use SimpleIT\ClaireExerciseBundle\Model\Resources\MetadataResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectConstraints;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ModelObject\ObjectId;
use SimpleIT\ClaireExerciseBundle\Repository\Exercise\SharedEntity\SharedEntityRepository;

/**
 * ExerciseResource repository
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseResourceRepository extends SharedEntityRepository
{
    /**
     * Find a resource by id
     *
     * @param mixed $resourceId
     *
     * @return ExerciseResource
     * @throws NonExistingObjectException
     */
    public function find($resourceId, $lockMode = null, $lockVersion = null)
    {
        $resource = parent::find($resourceId);
        if ($resource === null) {
            throw new NonExistingObjectException();
        }

        return $resource;
    }

    /**
     * Get all the exercise resources matching constraints
     *
     * @param ObjectConstraints $objectConstraints
     * @param AskerUser $owner
     * @param bool $publicOnly True if only the public resources must be returned
     *
     * @return array An array of ExerciseResource which match the constraints
     */
    public function findResourcesFromConstraintsByOwner(
        ObjectConstraints $objectConstraints,
        AskerUser $owner,
        $publicOnly = false
    )
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.children', 'c')
            ->select();

        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq('r.owner', $owner->getId()),
                    $qb->expr()->eq('r.archived', "false")
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('c.owner', $owner->getId()),
                    $qb->expr()->eq('c.archived', "false")
                )
            )
        );

        // Type of the resources (if any specified)
        if ($objectConstraints->getType() != null) {
            $qb->andWhere(
                $qb->expr()->eq(
                    'r.type',
                    "'"
                    . $objectConstraints->getType()
                    . "'"
                )
            );
        }

        // Public only
        if ($publicOnly) {
            $qb->andWhere(
                $qb->expr()->eq(
                    'r.public',
                    "true"
                )
            );
        }

        // Metadata
        $queryMetadataPart = '';
        $numberOfMetadataConstraints = 0;

        $metadataConstraints = $objectConstraints->getMetadataConstraints();

        foreach ($metadataConstraints as $mdc) {
            $this->addMetadataConstraintFromMdc(
                $qb,
                $queryMetadataPart,
                $mdc
            );
            $numberOfMetadataConstraints++;
        }

        // Excluded resources
        $queryExcludedPart = '';
        $excludedList = $objectConstraints->getExcluded();

        $numberOfExcluded =
            $this->addExcluded(
                $qb,
                $queryExcludedPart,
                $excludedList
            );

        // Finalization of the query
        $this->finalizeConstraintsQuery(
            $qb,
            $numberOfMetadataConstraints,
            $numberOfExcluded,
            $queryMetadataPart,
            $queryExcludedPart
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * Finalize the query by adding the query parts and calculating the product
     * in the count
     *
     * @param QueryBuilder $qb
     * @param int $numberOfMetadataConstraints
     * @param int $numberOfExcluded
     * @param string $queryMetadataPart
     * @param string $queryExcludedPart
     */
    private function finalizeConstraintsQuery(
        &$qb,
        $numberOfMetadataConstraints,
        $numberOfExcluded,
        $queryMetadataPart,
        $queryExcludedPart
    )
    {
        // Add metadata part of the query if there are metadata constraints
        if ($numberOfMetadataConstraints > 0) {
            $qb->andWhere($queryMetadataPart);
        }

        // Add excluded part of the query if there are excluded
        if ($numberOfExcluded > 0) {
            $qb->andWhere($queryExcludedPart);
        }

        // If there is any part to add to the query, add the "group by" clause
        if ($queryMetadataPart != '') {
            $qb->addGroupBy('r.id')
                ->having
                (
                    $qb->expr()->eq
                        (
                            $qb->expr()->count('r.id'),
                            $numberOfMetadataConstraints
                        )
                );
        }
    }

    /**
     * Add a metadata constraint to a query part from a MetadataConstraint
     * object
     *
     * @param QueryBuilder $qb
     * @param string $queryPart
     * @param MetadataConstraint $mdc
     */
    private function addMetadataConstraintFromMdc(
        &$qb,
        &$queryPart,
        MetadataConstraint $mdc
    )
    {
        $comp = $mdc->getComparator();
        $metaKey = $mdc->getKey();
        $val = $mdc->getValues();

        // in, between or exists ($val == null)
        if ($comp == 'in' || $comp == 'exists' || $comp == 'between') {
            $this->addMetadataConstraint(
                $qb,
                $queryPart,
                $metaKey,
                $comp,
                $val
            );
        } // lte, lt, gte, gt
        else {
            $this->addMetadataConstraint(
                $qb,
                $queryPart,
                $metaKey,
                $comp,
                $val[0]
            );
        }
    }

    /**
     * Completes the part of the request about metadata. Insert the LEFT JOIN in the
     * request if metadata constraints are added.
     *
     * @param QueryBuilder $qb The queryBuilder
     * @param string $queryPart The query part about metadata to be completed
     * @param string $metaKey The metadata key
     * @param string $comparison Name of a comparison function of Expr from queryBuilder ('in', 'eq', 'lte', 'lt', 'gte' or 'gt')
     * @param mixed $value The value or array of value
     */
    private function addMetadataConstraint(
        &$qb,
        &$queryPart,
        $metaKey,
        $comparison,
        $value
    )
    {
        // if exists (key test only)
        if ($comparison == 'exists') {
            $qp = $qb->expr()->eq('m.key', "'" . $metaKey . "'");
        } //if keyword(value test)
        else{
            if($comparison == 'keyword') {
                $qp= $qb->expr()->andX(
                    $qb->expr()->eq('m.key', "'" . MetadataResource::MISC_METADATA_KEY . "'"),
                    $qb->expr()->eq('m.value', "'" . $value . "'")
                );
            }       
            //else (key and value test)
            else {
                // if between
                if ($comparison == 'between') {
                    $exprComp = $qb->expr()->between('m.value', $value[0], $value[1]);
                } // if not between
                else {
                    $exprComp = $qb->expr()->$comparison('m.value', $value);
                }

                // AND of value test and key test
                $qp = $qb->expr()->andX(
                    $qb->expr()->eq('m.key', "'" . $metaKey . "'"),
                    $exprComp
                );
            }
        }

        // add to the query part
        if ($queryPart == '') {
            $queryPart = $qp;
            $qb->leftJoin(
                'r.metadata',
                'm'
            );
        } else {
            $queryPart = $qb->expr()->orX(
                $queryPart,
                $qp
            );
        }
    }

    /**
     * Completes the part of the request about excluded. Insert the LEFT JOIN in the
     * request if exclude constraints are added.
     *
     * @param QueryBuilder $qb The queryBuilder
     * @param string $queryPart The query part about excluded to be completed
     * @param array $excludedList An array of ObjectId that are excluded
     *
     * @return int The number of criteria that are added to the request part
     */
    private function addExcluded(
        &$qb,
        &$queryPart,
        array $excludedList
    )
    {
        $numberOfExcluded = 0;

        foreach ($excludedList as $objId) {
            /** @var ObjectId $objId */
            $qp = $qb->expr()->neq('r.id', $objId->getId());

            if ($queryPart == '') {
                $queryPart = $qp;
            } else {
                $queryPart = $qb->expr()->andX($queryPart, $qp);
            }

            $numberOfExcluded++;
        }

        return $numberOfExcluded;
    }

    /**
     * Find a knowledge if it is owned by the user
     *
     * @param int $entityId
     * @param AskerUser $owner
     *
     * @return mixed
     * @throws NonExistingObjectException
     */
    public function findByIdAndOwner($entityId, AskerUser $owner)
    {
        $queryBuilder = $this->createQueryBuilder('r');

        $queryBuilder->where($queryBuilder->expr()->eq('r.owner', $owner->getId()));
        $queryBuilder->andWhere($queryBuilder->expr()->eq('r.id', $entityId));

        $result = $queryBuilder->getQuery()->getResult();

        if (empty($result)) {
            throw new NonExistingObjectException('Unable to find resource ' . $entityId .
                ' for owner ' . $owner->getId());
        } else {
            return $result[0];
        }
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
