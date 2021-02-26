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
 */
class ProfileController extends BaseController
{

    /**
     * ANR COMPER
     * Create a JWT token and request the profile of a learner.
     * Then simply return this profile.
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
        $result         = $profileService->requestProfile($token);
        $response       = new JsonResponse($result);
        return $response;
    }

    /**
     * ANR COMPER
     * Creates a ComperProfileTrace corresponding to an "action" done by the learner regarding his profile.
     * An action can be, for example, "request", for when the learner requests his profile. 
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
