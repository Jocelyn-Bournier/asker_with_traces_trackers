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
use Symfony\Component\Config\FileLocator;
use \Firebase\JWT\JWT;

/**
 * Service which manages jwt tokens
 *
 * @author Rémi Casado <remi.casado@protonmail.com>
 */
class JwtService
{

    private $keyFilename;
    private $rootDir;

    function __construct($keyFilename, $rootDir)
    {
        $this->keyFilename = $keyFilename;
        $this->rootDir     = $rootDir;
    }

    /**
     * Get a jwt token containing the payload passed as parameter.
     *
     * @param Array $payload
     */
    public function getToken(Array $payload)
    {
        $configDirectories = array($this->rootDir.'/config');
        $fileLocator       = new FileLocator($configDirectories);
        $file  = $fileLocator->locate($this->keyFilename, null, true);
        $key   = file_get_contents($file);
        $token = JWT::encode($payload, $key, 'RS256');

        return $token;
    }

}
?>