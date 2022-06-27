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

namespace SimpleIT\ClaireExerciseBundle\Model\ExerciseObject;

use Doctrine\Common\Collections\Collection;
use SimpleIT\ClaireExerciseBundle\Entity\ExerciseResource\Metadata;
use SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseObject\ExerciseObject;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\CommonResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\MultipleChoiceQuestionResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\OpenEndedQuestionResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\PictureResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\SequenceResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ExerciseResource\TextResource;
use SimpleIT\ClaireExerciseBundle\Model\Resources\ResourceResource;

/**
 * Factory to create ExerciseObject from resources.
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ExerciseObjectFactory
{
    /**
     * Translate a CommonExercise + metadata into an ExerciseObject
     *
     * @param CommonResource $resource
     * @param Collection     $metadata
     * @param array          $requiredResource
     * @param null           $resourceId
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\InvalidTypeException
     * @return ExerciseObject The output exercise object
     */
    public static function createExerciseObject(
        CommonResource $resource,
        Collection $metadata = null,
        array $requiredResource = null,
        $resourceId = null
    )
    {
        // Depending on the type of the resource, call to the right factory
        switch (get_class($resource)) {
            case ResourceResource::MULTIPLE_CHOICE_QUESTION_CLASS:
                /** @var MultipleChoiceQuestionResource $resource */
                $object = MultipleChoiceQuestionFactory::createFromCommonResource($resource);
                break;
            case ResourceResource::OPEN_ENDED_QUESTION_CLASS:
                /** @var OpenEndedQuestionResource $resource */
                $object = OpenEndedQuestionFactory::createFromCommonResource($resource);
                break;
            case ResourceResource::TEXT_CLASS:
                /** @var TextResource $resource */
                $object = ExerciseTextFactory::createFromCommonResource($resource);
                break;

            case ResourceResource::PICTURE_CLASS:
                /** @var PictureResource $resource */
                $object = ExercisePictureFactory::createFromCommonResource($resource);
                break;

            case ResourceResource::SEQUENCE_CLASS:
                /** @var SequenceResource $resource */
                $object = ExerciseSequenceFactory::createFromCommonResource(
                    $resource,
                    $requiredResource
                );
                break;
            case ResourceResource::TEXT_WITH_HOLES_CLASS:
                /** @var SequenceResource $resource */
                $object = ExerciseTextWithHolesFactory::createFromCommonResource(
                    $resource
                );
                break;
            default:
                throw new InvalidTypeException(
                    'Resource type is incorrect: ' . get_class($resource)
                );
        }

        // add the metadata
        if ($metadata !== null) {
            foreach ($metadata as $md) {
                /** @var Metadata $md */
                $object->addMetadata($md->getKey(), $md->getValue());
            }
        }

        // add the resource id
        $object->setOriginResource($resourceId);

        // formulas
        if ($resource->getFormulas() !== null) {
            $object->setFormulas($resource->getFormulas());
        }

        return $object;
    }
}
