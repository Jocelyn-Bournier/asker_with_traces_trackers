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
use Symfony\Component\HttpFoundation\Request;

/**
 * Recommendation Controller
 *
 * ANR COMPER
 * @author Rémi Casado <remi.casado@protonmail.com>
 */
class RecommendationController extends BaseController
{

    /**
     * Récupère les recommendations d'exercices pour l'apprenant en fonction de son profil
     * @param directory Le repertoire sur lequel les recommendations sont proposées
     */
    function requestRecommendationsAction($directoryId = null, $fwid, $objectives) {
        $recommEngineUrl = "https://comper.projet.liris.cnrs.fr/sites/profile-engine/api/recommandations/generate/";

        $objectives = json_decode($objectives);
        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.token_storage')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+3000;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "role"     => 'learner',
            "username" => $user->getUsername(),
            "fwid"     => (int) $fwid,
            "objectives"=> $objectives,
            "exp" => $timestamp
        ];

        $token = $jwtEncoder->getToken($payload);

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Accept-Language : *';
        $header[] = 'Accept-Charset : *';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $recommEngineUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
        if($response === false)
        {
            echo 'Erreur Curl asker : ' . curl_error($curl);
        }
        return new JsonResponse($response);
    }

    /**
     * Récupère les recommendations d'exercices pour l'apprenant en fonction de son profil
     * @param directory Le repertoire sur lequel les recommendations sont proposées
     */
    function obtainRecommendationsAction($directoryId = null, $fwid) {
        $recommEngineUrl = "https://comper.projet.liris.cnrs.fr/sites/profile-engine/api/recommandations/retrieve/";

        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.token_storage')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+3000;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "role"     => 'learner',
            "fwid"     => intval($fwid),
            "username" => $user->getUsername(),
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/'
        ];
        $token = $jwtEncoder->getToken($payload);

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Accept-Language : *';
        $header[] = 'Accept-Charset : *';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $recommEngineUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
        if($response === false)
        {
            echo 'Erreur Curl asker : ' . curl_error($curl);
        }
        return new JsonResponse($response);
    }

    /**
     * Récupère les objectifs de la classe
     * @param directory Le repertoire sur lequel les recommendations sont proposées
     */
    function retrieveClassObjectivesAction($directoryId = null, $fwid) {
        $recommEngineUrl = "https://comper.projet.liris.cnrs.fr/sites/profile-engine/api/recommandations/classObjectives/";

        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.token_storage')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+3000;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "role"     => 'learner',
            "fwid"     => intval($fwid),
            "username" => $user->getUsername(),
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/'
        ];
        $token = $jwtEncoder->getToken($payload);

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Accept-Language : *';
        $header[] = 'Accept-Charset : *';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $recommEngineUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
        if($response === false)
        {
            echo 'Erreur Curl asker : ' . curl_error($curl);
        }
        return new JsonResponse($response);
    }

    /**
     * Récupère les objectifs de l'élève
     * @param directory Le repertoire sur lequel les recommendations sont proposées
     */
    function retrieveObjectivesAction($directoryId = null, $fwid) {
        $recommEngineUrl = "https://comper.projet.liris.cnrs.fr/sites/profile-engine/api/recommandations/objectives/";

        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.token_storage')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+3000;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "role"     => 'learner',
            "fwid"     => intval($fwid),
            "username" => $user->getUsername(),
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/'
        ];
        $token = $jwtEncoder->getToken($payload);

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Accept-Language : *';
        $header[] = 'Accept-Charset : *';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $recommEngineUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
        if($response === false)
        {
            echo 'Erreur Curl asker : ' . curl_error($curl);
        }
        return new JsonResponse($response);
    }

    /**
     * Enregistre les objectifs de l'élève
     * @param directory Le repertoire sur lequel les recommendations sont proposées
     */
    function saveObjectivesAction($directoryId = null, $fwid, $objectives) {
        $recommEngineUrl = "https://comper.projet.liris.cnrs.fr/sites/profile-engine/api/recommandations/saveObjectives/";

        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.token_storage')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+3000;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "role"     => 'learner',
            "fwid"     => intval($fwid),
            "username" => $user->getUsername(),
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/',
            "objectives" => $objectives
        ];
        $token = $jwtEncoder->getToken($payload);

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Accept-Language : *';
        $header[] = 'Accept-Charset : *';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $recommEngineUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
        if($response === false)
        {
            echo 'Erreur Curl asker : ' . curl_error($curl);
        }
        return new JsonResponse($response);
    }

    /**
     * Enregistre les objectifs de l'élève
     * @param directory Le repertoire sur lequel les recommendations sont proposées
     */
    function performRecommendationAction($directoryId = null, $fwid, $recommendation) {
        $recommEngineUrl = "https://comper.projet.liris.cnrs.fr/sites/profile-engine/api/recommandations/perform/";

        $jwtEncoder = $this->container->get('app.jwtService');
        $user       = $this->get('security.token_storage')->getToken()->getUser();
        $timestamp  = new \DateTime();
        $timestamp  = $timestamp->getTimestamp()+3000;
        $payload    = [
            "user"     => "asker:".$user->getId(),
            "role"     => 'learner',
            "fwid"     => intval($fwid),
            "username" => $user->getUsername(),
            "exp"      => $timestamp,
            "platform" => 'asker',
            "homepage" => 'https://asker.univ-lyon1.fr/',
            "recommendation" => $recommendation
        ];
        $token = $jwtEncoder->getToken($payload);

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Accept-Language : *';
        $header[] = 'Accept-Charset : *';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $recommEngineUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
        if($response === false)
        {
            echo 'Erreur Curl asker : ' . curl_error($curl);
        }
        return new JsonResponse($response);
    }

}
?>