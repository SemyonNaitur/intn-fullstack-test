<?php

class Validator
{
    /**
     * @return bool|array true on success or error bag array on failure
     */
    public static function validate(array $record, array $rules)
    {
        $errors = [];
        foreach ($rules as $fld_name => $fld_rules) {
            $fld_rules = (is_array($fld_rules)) ? $fld_rules : explode('|', $fld_rules);
            $val = $record[$fld_name] ?? '';
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
            }
        }
        return empty($errors) ?: $errors;
    }


    //--- rules ---//

    public static function string_rule($val)
    {
        return is_string($val) ?: 'Must be a string.';
    }

    public static function number_rule($val)
    {
        return is_numeric($val) ?: 'Must be a number.';
    }

    public static function integer_rule($val)
    {
        return ((int) $val == $val) ?: 'Must be an integer.';
    }

    public static function email_rule($val)
    {
        return (bool) filter_var($val, FILTER_VALIDATE_EMAIL) ?: 'Invalid email address.';
    }
    //--- /rules ---//


}
