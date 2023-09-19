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
use SimpleIT\ClaireExerciseBundle\Entity\Directory;

/**
 * Service which manages profiles
 *
 * @author Rémi Casado <remi.casado@protonmail.com>
 */
class ProfileService
{

    private $profileEndpoint;
    private $profileCreateEndpoint;
    private $groupCreateEndpoint;
    private $teacherManagerEndpoint;
	private $jwtService;

    function __construct($profileEndpoint, $profileCreateEndpoint, $groupCreateEndpoint, $teacherManagerEndpoint, $jwtService)
    {
        $this->profileEndpoint = $profileEndpoint;
        $this->profileCreateEndpoint = $profileCreateEndpoint;
        $this->groupCreateEndpoint = $groupCreateEndpoint;
        $this->teacherManagerEndpoint = $teacherManagerEndpoint;
        $this->jwtService = $jwtService;
    }

    /**
     * Set the role of the teacher on the profile engine
     *
     * @param string $token the profile to edit
     */
    public function setRole($token){
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Authorization: Bearer '.$token;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->teacherManagerEndpoint);
        curl_setopt($curl, CURLOPT_PUT, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($curl);
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
        return curl_exec($curl);
    }

    /**
     * Request the updated profile from the profile engine.
     *
     * @param string $token the profile to request
     */
    public function updateProfile($token)
    {
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Authorization: Bearer '.$token;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->profileEndpoint.'?update=true');
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($curl);
    }

    /**
     * Create a profile from the profile engine.
     *
     * @param string $token the profile to create
     */
    public function createProfile($token)
    {
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Authorization: Bearer '.$token;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->profileCreateEndpoint);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($curl);

    }

    /**
     * Create a group from the profile engine.
     *
     * @param string $token the profile to create
     */
    public function createGroup($token)
    {
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Response-Type: application/json';
        $header[] = 'Comper-origin: asker';
        $header[] = 'Authorization: Bearer '.$token;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->groupCreateEndpoint);
        curl_setopt($curl, CURLOPT_PUT, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


		// debug curl php
		//curl_setopt($curl, CURLOPT_VERBOSE, true);
		//$streamVerboseHandle = fopen('/tmp/debugcurl', 'w+');
		//curl_setopt($curl, CURLOPT_STDERR, $streamVerboseHandle);
		//  fin debug
        return curl_exec($curl);

    }

	// il se peut $directory soit parfois une ressource
	public function createGroupPayload( Directory $directory)
	{
		if (!$directory->getParent()){
			if ($directory->getFrameworkId() !== null){
				$timestamp  = new \DateTime();
				$timestamp  = $timestamp->getTimestamp()+3000;
				$payload    = [
				    "fwid"     => intval($directory->getFrameworkId()),
				    "groupName" => 'Asker : '.$directory->getName(),
				    "platform" => 'asker',
				    "platformGroupId" => 'asker:group-'.$directory->getId().'-'.$directory->getFrameworkId(),
					"students" => $this->userConverter($directory->getStudents(), "student"),
					"teachers" => $this->userConverter($directory->getTeachers(), "teacher")
				];
				return $this->createGroup( $this->jwtService->getToken($payload));
			}
		}
		return false;

	}
	public function userConverter($users, $type)
	{
		$json = array();
		foreach($users as $user){
			$u = new \stdClass();
			if ($type == "student"){
				$u->user = "asker:$user->getId()";
				$u->username = $user->getUsername();
				$u->forename = $user->getFirstName();
			}
			$u->name = $user->getLastName();
			$json[] = $u;
		}
		return $json;
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
    }

}
?>

