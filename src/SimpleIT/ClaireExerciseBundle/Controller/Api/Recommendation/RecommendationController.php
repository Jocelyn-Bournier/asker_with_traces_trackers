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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\Recommendation;
use Symfony\Component\HttpFoundation\JsonResponse;

use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
/**
 * API Recommendation Controller
 *
 * @author Rémi Casado <remi.casado@protonmail.com>
 */
class RecommendationController extends BaseController
{

    public function sendStatementAction($recommendationTitle)
    {
        $statementFactory = $this->container->get('app.statementFactoryService');
        $user             = $this->get('security.context')->getToken()->getUser();
        $statement        = $statementFactory->generateRecommendationClickStatement($user, $recommendationTitle);
        $response         = $statementFactory->sendStatements($statement); 
        $response = new JsonResponse($response);
        return $response;
    }

}
?>