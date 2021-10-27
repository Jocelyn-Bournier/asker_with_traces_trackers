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
use SimpleIT\ClaireExerciseBundle\Entity\ComperRecommendation;
/**
 * Recommendation Controller
 *
 * ANR COMPER
 * @author Rémi Casado <remi.casado@protonmail.com>
 */
class RecommendationController extends BaseController
{

    /**
     * ANR COMPER
     * 
     * Save a ComperRecommendationTrace corresponding to the action of clicking on a recommendation link as a learner.
     *
     * @OA\Post(
     *          path="/api/recommendations/{directoryId}/{title}",
     *          @OA\Parameter(in="path", name="directoryId", parameter="directoryId"),
     *          @OA\Parameter(in="path", name="title", parameter="title"),
     *          @OA\Response(response="200", description="confirmation of trace creation"),
     *     tags={"recommendations"},
     *      )
     * 
     * Note : Originally sent a statement to the comper LRS.
     */
    public function sendStatementAction($directoryId, $title)
    {
        $location = $this->get('request')->get('location');
        $user     = $this->get('security.context')->getToken()->getUser();
        $recomm   = new ComperRecommendationTrace();
        $recomm->setCreatedAt(new \DateTime());
        $recomm->setUser($user);
        $recomm->setContextDirectory($directoryId);
        $recomm->setResourceLocation($location);
        $recomm->setResourceTitle($title);
        $this->getDoctrine()->getEntityManager()->persist($recomm);
        $this->getDoctrine()->getEntityManager()->flush();
        $response         = new JsonResponse('Recommendation trace created');
        return $response;
    }

}
?>