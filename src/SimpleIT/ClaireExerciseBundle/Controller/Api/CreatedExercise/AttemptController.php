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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\CreatedExercise;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Collection\CollectionInformation;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AttemptResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\AttemptResourceFactory;

/**
 * API Attempt controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class AttemptController extends BaseController
{
    /**
     * Get a specific Attempt resource
     *
     * @param int $attemptId
     *
     * @throws ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($attemptId)
    {
        try {
            $attempt = $this->get('simple_it.exercise.attempt')->get(
                $attemptId,
                $this->getUserId()
            );
            $attemptResource = AttemptResourceFactory::create($attempt);

            return new ApiGotResponse($attemptResource, array("attempt", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(AttemptResource::RESOURCE_NAME);
        }
    }

    /**
     * Get the list of attempts
     *
     * @param CollectionInformation $collectionInformation
     *
     * @throws ApiBadRequestException
     * @return ApiGotResponse
     */
    public function listAction(CollectionInformation $collectionInformation)
    {
        $attempts = $this->get('simple_it.exercise.attempt')->getAll(
            $collectionInformation,
            $this->getUserId()
        );

        $attemptsResources = AttemptResourceFactory::createCollection($attempts);

        return new ApiGotResponse($attemptsResources, array('list', 'Default'));
    }
}
