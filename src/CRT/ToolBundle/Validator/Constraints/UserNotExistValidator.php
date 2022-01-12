<?php

namespace CRT\ToolBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserNotExistValidator extends ConstraintValidator
{
    private $ldap;

    public function __construct($ldap){
        $this->ldap = $ldap;
    }
    public function validate($email, Constraint $constraint)
    {
        $mailFilter = "(&(mail=$email)(objectClass=comptetec))";
        $users = $this->ldap->ldapParse(
            array('mail'),
            $this->ldap->getBaseUser(),
            $mailFilter
        );
        if ($users['count'] != 0){
            $this->context->addViolation($constraint->message,
                array(
                    '%email%' => $email,
                )
            );
        }
    }
}
