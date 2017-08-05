<?php

namespace JamboBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validator constraints
 */
class ContainsPesel extends Constraint
{
    /** @var string */
    public $message = 'This is not a valid PESEL number.';
}
