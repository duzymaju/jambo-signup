<?php

namespace JamboBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validator constraints
 */
class UniqueEntities extends Constraint
{
    /** @var string */
    public $message = 'This value is already used.';

    /** @var string */
    public $field;

    /** @var bool */
    public $ignoreNull = false;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'unique_entities';
    }
}
