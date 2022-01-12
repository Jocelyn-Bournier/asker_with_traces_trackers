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

use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModel;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModelPosition;

/**
 * Class TestModelResourceFactory
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class TestModelResourceFactory
{

    /**
     * Create a TestModelResource collection
     *
     * @param array $testModels
     *
     * @return array
     */
    public static function createCollection(array $testModels)
    {
        $testModelResources = array();
        foreach ($testModels as $testModel) {
            $testModelResources[] = self::create($testModel);
        }

        return $testModelResources;
    }

    /**
     * Create a TestModelResource
     *
     * @param TestModel $testModel
     *
     * @return TestModelResource
     */
    public static function create(TestModel $testModel)
    {
        $testModelResource = new TestModelResource();
        $testModelResource->setId($testModel->getId());
        $testModelResource->setTitle($testModel->getTitle());

        $exerciseModels = array();
        foreach ($testModel->getTestModelPositions() as $position) {
            /** @var TestModelPosition $position */
            $exerciseModels[$position->getPosition()] = $position->getExerciseModel()->getId();
        }

        // order the model ids in a sequential array
        $em = array();
        for ($i = 0; $i < count($exerciseModels); $i++) {
            $em[] = $exerciseModels[$i];
        }
        $testModelResource->setExerciseModels($em);

        return $testModelResource;
    }
}
