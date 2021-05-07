<?php 
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsAlphabet extends Constraint
{
    public $message = 'The string "{{ string }}" Invalid name: it can only contain letters.';
}



?>