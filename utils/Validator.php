<?php

class Validator
{
    public static function validate(array $row, array $rules)
    {
        $errors = [];
        $row_valid = true;
        foreach ($rules as $fld_name => $fld_rules) {
            $fld_rules = (is_array($fld_rules)) ? $fld_rules : explode('|', $fld_rules);
            $val = $row[$fld_name] ?? '';
            $fld_errors = [];

            if (in_array('required', $fld_rules)) {
                if ($val === '') {
                    $fld_errors[] = 'Required field';
                }
            }

            if (!$fld_errors) {
                foreach ($fld_rules as $rule) {
                    $func = trim($rule) . '_rule';
                    if ($func == 'required_rule') continue;

                    if (!is_callable("static::$func")) {
                        throw new Exception("Invalid rule: $rule");
                    }

                    if (($valid = static::$func($val)) !== true) {
                        $fld_errors[] = $valid;
                    }
                }
            }

            if ($fld_errors) {
                $errors[$fld_name] = $fld_errors;
                $row_valid = false;
            }
        }
        return $row_valid ?: $errors;
    }

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
