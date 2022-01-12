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

namespace SimpleIT\ClaireExerciseBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiException;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Collection\Sort;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiCollectionParamConverter
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ApiCollectionParamConverter implements ParamConverterInterface
{

    /**
     * Apply
     *
     * @param Request        $request       Request
     * @param ParamConverter $configuration Configuration
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiException
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $collectionInformation = new CollectionInformation();

        $collectionInformation = $this->handleParameters($request, $collectionInformation);

        $request->attributes->set($configuration->getName(), $collectionInformation);

        return true;
    }

    /**
     * Handle parameters
     *  - sorts
     *  - filters
     *
     * @param Request               $request               Request
     * @param CollectionInformation $collectionInformation Collection information
     *
     * @throws ApiException
     * @return CollectionInformation
     */
    private function handleParameters(
        Request $request,
        CollectionInformation $collectionInformation
    )
    {
        try {
            $parameters = $request->query->all();

            // handle sort
            if (array_key_exists('sort', $parameters)) {

                /* Sort :
                 *      - sort=property order
                 *      - sort=property order,property order
                 * Order : asc | desc
                 */
                $sortParams = explode(',', $parameters['sort']);
                foreach ($sortParams as $sortParam) {
                    $sortItemConcats = explode(' ', $sortParam);
                    $order = 'asc';
                    if (isset($sortItemConcats[1])) {
                        $order = $sortItemConcats[1];
                    }
                    $sortItem = new Sort($sortItemConcats[0], $order);
                    $collectionInformation->addSort($sortItem);

                }
                unset($parameters['sort']);
            }

            if (!empty($parameters)) {
                $filterItems = array();
                foreach ($parameters as $property => $value) {
                    $value = explode(',', $value);
                    if (count($value) < 2) {
                        $value = $value[0];
                    }
                    if (!empty($value)) {
                        $filterItems[$property] = $value;
                    }
                }
                $collectionInformation->setFilters($filterItems);
            }

            return $collectionInformation;

        } catch (\InvalidArgumentException $iae) {
            throw new ApiException(ApiException::STATUS_CODE_BAD_REQUEST, $iae->getMessage());
        }
    }

    /**
     * Supports (only CollectionInformation)
     *
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(
        ParamConverter $configuration
    )
    {
        $isClassSupported = false;
        if ('SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation' ===
            $configuration->getClass()
        ) {
            $isClassSupported = true;
        }

        return $isClassSupported;
    }
}
