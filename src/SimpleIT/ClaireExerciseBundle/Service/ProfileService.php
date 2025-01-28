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
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->teacherManagerEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        return curl_exec($curl);
    }

    /**
     * Request the profile from the profile engine.
     *
     * @param string $token the profile to request
     */
    public function requestProfile($token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->profileEndpoint,
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
        return curl_exec($curl);
    }

    /**
     * Request the updated profile from the profile engine.
     *
     * @param string $token the profile to request
     */
    public function updateProfile($token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->profileEndpoint.'?update=true',
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
        return curl_exec($curl);
    }

    /**
     * Create a profile from the profile engine.
     *
     * @param string $token the profile to create
     */
    public function createProfile($token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->profileCreateEndpoint,
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
        return curl_exec($curl);

    }

    /**
     * Create a group from the profile engine.
     *
     * @param string $token the profile to create
     */
    public function createGroup($token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->groupCreateEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
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
		if (!$directory->getParent()) {
			if ($directory->getFrameworkId() !== null) {
				$timestamp  = new \DateTime();
				$timestamp  = $timestamp->getTimestamp()+3000;
				$payload    = [
				    "fwid"     => intval($directory->getFrameworkId()),
				    "groupName" => 'Asker : '.$directory->getName(),
				    "platform" => 'asker',
				    "platformGroupId" => 'asker:group-'.$directory->getId().'-'.$directory->getFrameworkId(),
					"students" => $this->userConverter($directory->getStudents(), "student"),
                    "teachers" => $this->userConverter(array_merge($directory->getTeachers(), $directory->getReaders()), "teacher"),
				];
				return $this->createGroup( $this->jwtService->getToken($payload));
			}
		}
		return false;

	}

    /*
     * @return a json array containing a COMPER's user struct
     */
	public function userConverter($users, $type)
	{
		$json = array();
		foreach($users as $user){
			$u = new \stdClass();
			switch ($type) {
				case 'student':
					$u->user = "asker:{$user->getUser()->getId()}";
					$u->forename = $user->getUser()->getFirstName();
					$u->name = $user->getUser()->getLastName();
					break;
				case 'teacher':
                    if ($user->getIsReader()){
                        $u->role = 'teacher_viewer';
                    }else{
					    $u->role = 'teacher_editor';
                    }
					break;
				default:
                    break;
			}
			$u->username = $user->getUser()->getUsername();
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
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->profileEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token,
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
    }

}
?>

