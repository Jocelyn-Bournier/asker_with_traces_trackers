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

namespace SimpleIT\ClaireExerciseBundle\Controller\Api\Profile;
use Symfony\Component\HttpFoundation\JsonResponse;
use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use SimpleIT\ClaireExerciseBundle\Entity\ComperProfileTrace;
/**
 * Profile Controller
 *
 * ANR COMPER
 * @author Rémi Casado <remi.casado@protonmail.com>
 * @author Valentin Lachand-Pascal <valentin@lachand.net>
 */
class ProfileController extends BaseController
{

    /**
     * ANR COMPER
     * Create a JWT token and request the profile of a learner.
     * Then simply return this profile.
     * @OA\Post(
     *          path="/api/profile/request/{frameworkId}",
     *          @OA\Parameter(in="path", name="frameworkId", parameter="frameworkId"),
     *          @OA\Response(response="200", description="profile of an user"),
     *     tags={"profile"},
     *      )
     * @param int $framework_id the identifier of the framework used
     * @return JsonResponse the profile of a learner on Json format
     */
    public function requestProfileAction($framework_id)
    {
        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.context')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+30;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "fwid"     => intval($framework_id),
            "username" => $user->getUsername(),
            "role"     => 'learner',
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/'
        ];

        $token = $jwtEncoder->getToken($payload);
        
        $profileService = $this->container->get('app.profileService');
        $profile = new JsonResponse($profileService->requestProfile($token));

        return $profile;
    }

    /**
     * ANR COMPER
     * Creates a ComperProfileTrace corresponding to an "action" done by the learner regarding his profile.
     * An action can be, for example, "request", for when the learner requests his profile.
     * @OA\Get(
     *          path="/api/profile/trace/{directoryId}/{action}",
     *          @OA\Parameter(in="path", name="directoryId", parameter="directoryId"),
     *          @OA\Parameter(in="path", name="action", parameter="action"),
     *          @OA\Response(response="200", description="confirmation of trace creation"),
     *     tags={"profile"},
     *      )
     * @param $action string the kind of action performed by the learner
     * @param $directoryId int the identifier of the directory where the action is applied
     * @return JsonResponse return a JsonResponse 'Profile trace created' after the trace was added to the user's profile
     */
    public function traceAction($directoryId, $action)
    {
        $user     = $this->get('security.context')->getToken()->getUser();
        $profile  = new ComperProfileTrace();
        $profile->setCreatedAt(new \DateTime());
        $profile->setUser($user);
        $profile->setContextDirectory($directoryId);
        $profile->setAction($action);
        $this->getDoctrine()->getEntityManager()->persist($profile);
        $this->getDoctrine()->getEntityManager()->flush();
        $response         = new JsonResponse('Profile trace created');
        return $response;
    }

}
?>
