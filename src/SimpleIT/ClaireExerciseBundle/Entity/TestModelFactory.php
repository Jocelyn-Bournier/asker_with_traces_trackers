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

namespace SimpleIT\ClaireExerciseBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use SimpleIT\ClaireExerciseBundle\Entity\Test\TestModel;
use SimpleIT\ClaireExerciseBundle\Model\Resources\TestModelResource;

/**
 * Class to manage the creation of TestModel
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
abstract class TestModelFactory
{
    /**
     * Create a new TestModel object
     *
     * @param string $title
     * @param User   $author
     *
     * @return TestModel
     */
    public static function createExerciseModel($title = '', $author = null)
    {
        $testModel = new TestModel();
        $testModel->setAuthor($author);
        $testModel->setTitle($title);

        return $testModel;
    }

    /**
     * Create an exerciseModel entity from a resource and the author
     *
     * @param TestModelResource $testModelResource
     *
     * @return TestModel
     */
    public static function createFromResource(
        TestModelResource $testModelResource
    )
    {
        $testModel = new TestModel();
        $testModel->setTitle($testModelResource->getTitle());

        return $testModel;
    }
}
