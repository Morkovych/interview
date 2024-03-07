<?php

namespace FigureValidators;

use Exception;

class ValidatorHelper
{
    /**
     * @throws Exception
     */
    public static function getValidator($className): object
    {
        return match ($className) {
            'Pawn' => new ValidatorPawnMove(),
            default => throw new Exception('Validator not found.')
        };
    }
}