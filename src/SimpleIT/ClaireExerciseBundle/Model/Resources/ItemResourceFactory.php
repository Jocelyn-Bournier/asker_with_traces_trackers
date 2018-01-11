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

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use SimpleIT\ClaireExerciseBundle\Entity\CreatedExercise\Item;
use SimpleIT\ClaireExerciseBundle\Serializer\Handler\AbstractClassForExerciseHandler;

/**
 * Class ExerciseModelResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class ItemResourceFactory
{

    /**
     * Create an ItemResource collection
     *
     * @param array $items
     *
     * @return array
     */
    public static function createCollection(array $items)
    {
        $itemResources = array();
        foreach ($items as $item) {
            $itemResources[] = self::create($item, null);
        }

        return $itemResources;
    }

    /**
     * Create an Exercise Resource
     *
     * @param Item $item
     *
     * @return ItemResource
     */
    public static function create(Item $item)
    {
        $itemResource = new ItemResource();

        $itemResource->setItemId($item->getId());
        $itemResource->setType($item->getType());

        $serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerSubscribingHandler(
                        new AbstractClassForExerciseHandler()
                    );
                }
            )
            ->build();
        $content = $serializer->deserialize(
            $item->getContent(),
            ItemResource::getClass($item->getType()),
            'json'
        );

        $itemResource->setContent($content);

        return $itemResource;
    }
}
