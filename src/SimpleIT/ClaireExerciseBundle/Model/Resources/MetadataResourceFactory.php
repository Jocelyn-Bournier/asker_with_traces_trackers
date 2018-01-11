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

namespace SimpleIT\ClaireExerciseBundle\Model\Resources;

use SimpleIT\ClaireExerciseBundle\Entity\SharedEntity\Metadata;

/**
 * Class ResourceMetadataResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class MetadataResourceFactory
{
    /**
     * Create a Metadata Resources collection
     *
     * @param mixed $metadatas Metadatas
     *
     * @return array
     */
    public static function createCollection($metadatas = array())
    {
        $metadataResources = array();
        foreach ($metadatas as $metadata) {
            /** @var Metadata $metadata */
            $metadataResources[] = self::create($metadata);
        }

        return $metadataResources;
    }

    /**
     * Create Metadata Resource
     *
     * @param Metadata $metadata Metadata
     *
     * @return MetadataResource
     */
    public static function create($metadata)
    {
        $metadataResource = new MetadataResource();
        $metadataResource->setKey($metadata->getKey());
        $metadataResource->setValue($metadata->getValue());

        return $metadataResource;
    }

    /**
     * Create Metadata Resource
     *
     * @param string $key
     * @param string $value
     *
     * @return MetadataResource
     */
    public static function createFromKeyValue($key, $value)
    {
        $metadataResource = new MetadataResource();
        $metadataResource->setKey($key);
        $metadataResource->setValue($value);

        return $metadataResource;
    }
}
