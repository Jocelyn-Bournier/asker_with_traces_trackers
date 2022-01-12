<?php

namespace CRT\ToolBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class UserNotExist extends Constraint
{
    public $message = "Il existe déjà un utilisateur avec l'adresse : %email%.";


    public function validatedBy()
    {
        return 'user_not_exist';
    }
}
