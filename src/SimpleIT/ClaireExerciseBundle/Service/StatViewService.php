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
use SimpleIT\ClaireExerciseBundle\Entity\StatView;
/**
 * Service which manages the stored exercises
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class StatViewService extends TransactionalService
{
    /**
     * @var DirectoryRepository
     */
    private $statViewRepository;
    /**
     * Set statViewRepository
     *
     * @param UserRepository $statViewRepository
     */
    public function setStatViewRepository($statViewRepository)
    {
        $this->statViewRepository = $statViewRepository;
    }

    public function remove(StatView $view)
    {
        //if (
        //    $id->getOwner()->getId() == $user->getId()
        //    || $user->isAdmin()
        //){
            //try{
                $this->em->remove($view);
                $this->em->flush();
            //}catch (ForeignKeyConstraintViolationException $e){
            //}
        //}else{
        //    throw new AccessDeniedException();
        //}
    }

}

