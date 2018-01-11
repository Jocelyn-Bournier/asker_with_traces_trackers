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

namespace SimpleIT\ClaireExerciseBundle\Service\Serializer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use SimpleIT\ClaireExerciseBundle\Serializer\Handler\AbstractClassForExerciseHandler;

/**
 * Class Serializer
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class Serializer implements SerializerInterface
{
    /**
     * @var  string
     */
    public $defaultFormat;

    /**
     * @var  \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @var SerializationContext
     */
    private $serializationContext;

    /**
     * Constructor

     */
    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerSubscribingHandler(new AbstractClassForExerciseHandler());
                }
            )
            ->build();
        $this->serializationContext = SerializationContext::create();
        $this->serializationContext->enableMaxDepthChecks();
        $this->defaultFormat = 'json';
    }

    /**
     * Check is format is serializable / deserializable
     *
     * @param string $format Format
     *
     * @return bool
     */
    public static function isFormatSerializable($format)
    {
        $formatSerializable = true;
        if ('json' != $format) {
            $formatSerializable = false;
        }

        return $formatSerializable;
    }

    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array $data   Data
     * @param string       $format Format
     * @param array        $groups Groups of allowed fields
     *
     * @return string
     */
    public function serialize($data, $format = null, array $groups = array())
    {
        if (is_null($format)) {
            $format = $this->defaultFormat;
        }
        if (!empty($groups)) {
            $this->serializationContext->setGroups($groups);
        }

        return $this->serializer->serialize($data, $format, $this->serializationContext);
    }

    /**
     * Deserializes the given data to the specified type.
     *
     * @param string $data   Data
     * @param string $type   Type
     * @param string $format Format
     *
     * @return object|array
     */
    public function deserialize($data, $type, $format = null)
    {
        if (is_null($format)) {
            $format = $this->defaultFormat;
        }

        return $this->serializer->deserialize($data, $type, $format);
    }

    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array         $data
     * @param string               $format
     * @param SerializationContext $context
     *
     * @return string
     */
    public function jmsSerialize($data, $format, SerializationContext $context = null)
    {
        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * Deserializes the given data to the specified type.
     *
     * @param string                 $data
     * @param string                 $type
     * @param string                 $format
     * @param DeserializationContext $context
     *
     * @return object|array
     */
    public function jmsDeserialize($data, $type, $format, DeserializationContext $context = null)
    {
        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    /**
     * Get defaultFormat
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * Set defaultFormat
     *
     * @param string $defaultFormat
     *
     * @codeCoverageIgnore
     */
    public function setDefaultFormat($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * Get serializer
     *
     * @return \JMS\Serializer\SerializerInterface
     * @codeCoverageIgnore
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Set serializer
     *
     * @param \JMS\Serializer\SerializerInterface $serializer
     *
     * @codeCoverageIgnore
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }
}
