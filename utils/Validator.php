<?php

class Validator
{

    public static function string_rule($val)
    {
        return is_string($val) ?: 'Must be a string.';
    }

    public static function number_rule($val)
    {
        return is_numeric($val) ?: 'Must be a number.';
    }

    public static function email_rule($val)
    {
        return (bool) filter_var($val, FILTER_VALIDATE_EMAIL) ?: 'Invalid email address.';
    }
}
