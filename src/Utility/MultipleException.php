<?php
namespace App\Utility;

class MultipleException
{
    public static function entityError($errors)
    {
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return json_encode($messages);
        }

        return false;
    }
}
