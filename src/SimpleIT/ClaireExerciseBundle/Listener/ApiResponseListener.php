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

namespace SimpleIT\ClaireExerciseBundle\Listener;

use SimpleIT\ClaireExerciseBundle\Model\Api\ApiResponse;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class ApiResponseListener
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ApiResponseListener
{
    /**
     * Serialization service
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Handle ApiResponse
     *
     * @param FilterResponseEvent $event
     *
     * @throws \SimpleIT\ClaireExerciseBundle\Exception\Api\ApiException
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        if ($response instanceof ApiResponse) {
            $format = 'json';
            $contentType = 'application/json';

            $resource = $response->getResource();
            if ($format != 'html') {
                $resource = $this->serializer->serialize(
                    $resource,
                    $format,
                    $response->getGroups()
                );
            }
            $response->setContent($resource);
            $response->headers->set('Content-type', $contentType);
        }
    }

    /**
     * Set serializer
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
