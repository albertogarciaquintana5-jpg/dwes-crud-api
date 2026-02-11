<?php
namespace App\Validators;

class Validator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword($password, $min = 6) {
        return is_string($password) && strlen($password) >= $min;
    }

    public static function requiredFields($body, $fields) {
        $errors = [];
        foreach ($fields as $f) {
            if (empty($body[$f])) $errors[] = "$f is required";
        }
        return $errors;
    }
}
