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

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use SimpleIT\ClaireExerciseBundle\Exception\Api\ApiException;
use SimpleIT\ClaireExerciseBundle\Service\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiResourceParamConverter
 *
 * @author Romain Kuzniak <romain.kuzniak@simple-it.fr>
 */
class ApiResourceParamConverter implements ParamConverterInterface
{
    /**
     * @var  SerializerInterface
     */
    private $serializer;

    /**
     * Apply
     *
     * @param Request        $request       Request
     * @param ParamConverter $configuration Configuration
     *
     * @throws ApiException
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        try {
            // Array deserialization
            if ($configuration->getClass() == 'Doctrine\Common\Collections\ArrayCollection'
                || $configuration->getClass() == 'Doctrine\Common\Collections\Collection'
            ) {
                $options = $configuration->getOptions();

                if (array_key_exists('type', $options)) {
                    $type = 'ArrayCollection<' . $options['type'] . '>';
                    $resource = $this->serializer->deserialize(
                        $request->getContent(),
                        $type,
                        'json'
                    );
                } else {
                    $resource = $this->serializer->deserialize(
                        $request->getContent(),
                        'array',
                        'json'
                    );
                    $resource = new ArrayCollection($resource);
                }
            } else {
                $resource = $this->serializer->deserialize(
                    $request->getContent(),
                    $configuration->getClass(),
                    'json'
                );
            }
            $request->attributes->set($configuration->getName(), $resource);
            $apply = true;

        } catch (\Exception $e) {
            throw new ApiException(ApiException::STATUS_CODE_BAD_REQUEST, $e->getMessage());
        }

        return $apply;
    }

    /**
     * Supports
     *
     * @param ParamConverter $configuration Configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        } elseif ($configuration->getClass() === 'Doctrine\Common\Collections\ArrayCollection'
            || $configuration->getClass() === 'Doctrine\Common\Collections\Collection'
        ) {
            return true;
        } elseif (explode('\\', $configuration->getClass())[0] !== 'SimpleIT') {
            return false;
        }

        return true;
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
