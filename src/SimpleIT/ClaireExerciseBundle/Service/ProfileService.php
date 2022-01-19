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

namespace SimpleIT\ClaireExerciseBundle\Service;

/**
 * Service which manages profiles
 *
 * @author Rémi Casado <remi.casado@protonmail.com>
 */
class ProfileService
{

    private $profileEndpoint;
    private $profileCreateEndpoint;

    function __construct($profileEndpoint, $profileCreateEndpoint)
    {
        $this->profileEndpoint = $profileEndpoint;
        $this->profileCreateEndpoint = $profileCreateEndpoint;
    }

    /**
     * Request the profile from the profile engine.
     *
     * @param string $token the profile to request
     */
    public function requestProfile($token)
    {
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Authorization: Bearer '.$token;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->profileEndpoint);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if($response === false)
        {
            echo 'Erreur Curl asker: ' . curl_error($curl);
        } else {
            //echo $response;
            return $response;
        }
    }

    /**
     * Request the profile from the profile engine.
     *
     * @param string $token the profile to request
     */
    public function createProfile($token)
    {
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Authorization: Bearer '.$token;

        echo $this->profileCreateEndpoint;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->profileCreateEndpoint);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (curl_exec($curl) !== null) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Add the profile as a teacher for a repository in the profile engine.
     *
     * @param string $token the profile to request
     */
    public function addFrameworkTeacher($token)
    {
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Authorization: Bearer '.$token;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->profileEndpoint);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        echo $response;
    }

}
?>