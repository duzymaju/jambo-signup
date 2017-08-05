<?php

namespace JamboBundle\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use JamboBundle\Entity\Repository\BaseRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Validator constraints
 */
class UniqueEntitiesValidator extends ConstraintValidator
{
    /** @var array */
    private $repositories = [];

    /**
     * Add repository
     *
     * @param BaseRepositoryInterface $repository repository
     * @param string                  $entityName entity name
     */
    public function addRepository(BaseRepositoryInterface $repository, $entityName)
    {
        $this->repositories[$entityName] = $repository;
    }

    /**
     * Validate
     *
     * @param string     $value      value
     * @param Constraint $constraint constraint
     *
     * @throws ValidatorException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof ArrayCollection) || $value->count() < 1) {
            return;
        }

        $entityPath = explode('\\', get_class($value->first()));
        $entityName = array_pop($entityPath);
        if (!array_key_exists($entityName, $this->repositories)) {
            throw new ValidatorException(
                sprintf('There is no repository added for %s entity. Add it using addRepository method.', $entityName)
            );
        }
        $repository = $this->repositories[$entityName];

        $values = [];
        $violations = [];
        $getterName = 'get' . ucfirst($constraint->field);

        $i = 0;
        foreach ($value as $item) {
            $currentValue = $item->$getterName();
            if (!$constraint->ignoreNull || !empty($currentValue)) {
                if (in_array($currentValue, $values)) {
                    $this->addViolation($constraint, $i);
                    $violations[] = $i;
                }
                $values[$i] = $currentValue;
            }
            $i++;
        }

        $entities = $repository->findBy([
            $constraint->field => array_values(array_unique($values)),
        ]);
        foreach ($entities as $entity) {
            $currentValue = $entity->$getterName();
            $indexNo = array_search($currentValue, $values);
            if ($indexNo !== false && !in_array($indexNo, $violations)) {
                $this->addViolation($constraint, $indexNo);
            }
        }
    }

    /**
     * Add violation
     *
     * @param Constraint $constraint constraint
     * @param int        $itemNo     item no
     */
    private function addViolation(Constraint $constraint, $itemNo)
    {
        $this->context->buildViolation($constraint->message)
            ->atPath('[' . $itemNo . '].' . $constraint->field)
            ->addViolation();
    }
}
