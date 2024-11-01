<?php

namespace App\Validator\User;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Validation;

/**
 * The validator to validate phone of the user data
 */
class PhoneValidator
{
    /**
     * Validation executor
     *
     * @param mixed $phone
     * @return true
     *
     * @throws ConstraintDefinitionException
     */
    public function validate(mixed $phone): true
    {
        $idValidator = Validation::createValidator();
        $violations = $idValidator->validate($phone, [
            new Assert\NotNull(),
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length(['min' => 5, 'max' => 8])
        ]);

        if (count($violations) > 0) {
            throw new ConstraintDefinitionException(message: 'The user phone is invalid.');
        }

        return true;
    }
}