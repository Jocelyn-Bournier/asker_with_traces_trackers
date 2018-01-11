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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\ExerciseModel;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseModel\ExerciseModel;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException;
use SimpleIT\ClaireExerciseBundle\Exception\FilterException;
use SimpleIT\ClaireExerciseBundle\Exception\NonExistingObjectException;
use SimpleIT\ClaireExerciseBundle\Model\Api\ApiGotResponse;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseModelResourceFactory;

/**
 * API Exercise Model controller
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseModelAttemptedController extends BaseController
{
    /**
     * Get a specific exerciseModel resource
     *
     * @param int $exerciseModelId Exercise Model id
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiNotFoundException
     * @return ApiGotResponse
     */
    public function viewAction($exerciseModelId)
    {
        try {
            /** @var ExerciseModel $exerciseModel */
            $exerciseModel = $this->get('simple_it.exercise.exercise_model')
                ->getAllByUserWhoAttemptedByModel($this->getUserId(), $exerciseModelId);

            $exerciseModelResource = ExerciseModelResourceFactory::create($exerciseModel, true);

            return new ApiGotResponse($exerciseModelResource, array("details", 'Default'));

        } catch (NonExistingObjectException $neoe) {
            throw new ApiNotFoundException(ExerciseModelResource::RESOURCE_NAME);
        }
    }

    /**
     * Get the list of exercise models that the user answered
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiBadRequestException
     * @return ApiGotResponse
     */
    public function listAction()
    {
        try {
            $exerciseModels = $this->get('simple_it.exercise.exercise_model')
                ->getAllByUserWhoAttempted($this->getUserId());

            $exerciseModelResources = ExerciseModelResourceFactory::createCollection
                (
                    $exerciseModels,
                    true
                );
            #return $this->render(
            #  'debug.html.twig', array(
            #      #'exerciseModelResources' => json_encode($exerciseModelResources)
            #      )
            #  );
            return new ApiGotResponse($exerciseModelResources, array(
                'details',
                'Default'
            ));
              return $this->render(
                'debug.html.twig', array(
                    'exerciseModelResources' => json_encode($exerciseModelResources)
                    )
                );
        } catch (FilterException $fe) {
            throw new ApiBadRequestException($fe->getMessage());
        }
    }
}
