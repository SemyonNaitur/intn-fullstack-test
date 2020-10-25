<?php

namespace System;

class Validator
{
    protected ?DB $db = null;
    protected ?array $error_bag = null;
    protected array $default_opts = ['clear_error_bag' => false];

    public function __construct(DB $db = null)
    {
        $this->set_db($db);
    }

    /**
     * Rules array format:
     *  [
     *      'field_name' | 'field_name:field_label' => 'rule1|rule2|...' | ['rule1', 'rule2', ...]
     *  ]
     * 
     *  Rule format: 'rule_name' | 'rule_name:rule_params'
     * 
     * @param   array   $record
     * @param   array   $rules
     * @return  bool    true on success
     */
    public function validate(array $record, array $rules, $opts = [])
    {
        $opts = $this->check_opts($opts);
        $errors = [];
        foreach ($rules as $fld_name => $fld_rules) {
            [$fld_name, $label] = sscanf($fld_name, '%[^:]:%s');
            $fld_label = $label ?? 'value';

            $fld_rules = (is_array($fld_rules)) ? $fld_rules : explode('|', $fld_rules);
            $val = $record[$fld_name] ?? '';
            $fld_errors = [];

            if (in_array('required', $fld_rules)) {
                if ($val === '') {
                    $fld_errors[] = ($label) ? "$label is required." : 'Required field.';
                }
            }
            if (!$fld_errors) {
                foreach ($fld_rules as $i => &$rule) {
                    $rule = preg_replace('/\s/', '', $rule);
                    [$rule_name, $params] = sscanf($rule, '%[^:]:%s');
                    if ($rule_name == 'required') continue;

                    // Make sure unique rule is checked last, and only if value is valid.
                    if ($rule_name == 'unique') {
                        if ($i < (count($fld_rules) - 1)) {
                            $fld_rules[] = $rule;
                            continue;
                        } elseif ($fld_errors) {
                            break;
                        }
                    }

                    $func = $rule_name . '_rule';
                    if (!method_exists($this, $func)) {
                        throw new \Exception("Invalid rule name: $rule_name");
                    }

                    if (($valid = $this->$func($val, $params ?? '', $this->db)) !== true) {
                        $fld_errors[] = ucfirst(sprintf($valid, $fld_label));
                    }
                }
            }

            if ($fld_errors) {
                $errors[$fld_name] = $fld_errors;
            }
        }
        if ($errors) {
            $this->update_error_bag($errors);
            return false;
        } else {
            return true;
        }
    }

    public function set_db(DB $db)
    {
        $this->db = $db;
    }

    public function get_error_bag()
    {
        return $this->error_bag;
    }

    public function clear_error_bag()
    {
        $this->error_bag = null;
    }


    //--- rules ---//

    public static function string_rule($val)
    {
        return is_string($val) ?: '%s be a string.';
    }

    public static function number_rule($val)
    {
        return is_numeric($val) ?: '%s be a number.';
    }

    public static function integer_rule($val)
    {
        return ((int) $val == $val) ?: '%s be an integer.';
    }

    public static function email_rule($val)
    {
        return (bool) filter_var($val, FILTER_VALIDATE_EMAIL) ?: 'Invalid %s address.';
    }

    public static function date_rule($val)
    {
        return (bool) preg_match('/^\d{4}(-\d{2}){2}$/', $val) ?: 'Invalid date.';
    }

    public static function datetime_rule($val)
    {
        return (bool) preg_match('/^\d{4}(-\d{2}){2} \d{2}(:\d{2}){2}$/', $val) ?: 'Invalid date or time.';
    }

    /**
     * @param   mixed       $val
     * @param   string|int  $length
     * @return  bool|string
     */
    public static function min_length_rule($val, $len)
    {
        if ((int) $len != $len || $len < 0) {
            throw new \Exception('Invalid length passed to "min_length" rule');
        }
        return (mb_strlen((string) $val) >= $len) ?: "%s must be at least $len characters long.";
    }

    /**
     * @param   mixed       $val
     * @param   string      $params format: table.column
     * @param   DBUtil       $db
     * @return  bool|string
     */
    public static function unique_rule($val, string $params, ?DB $db)
    {
        if (!$db) throw new \Exception('An instance of DBUtil is required');
        [$tbl, $fld] = sscanf($params, '%[^.].%s');
        if (!$tbl) throw new \Exception('Params for "unique" rule are missing table name');
        if (!$fld) throw new \Exception('Params for "unique" rule are column table name');
        return $db->is_unique($val, $fld, $tbl) ?: '%s already exists.';
    }
    //--- /rules ---//


    protected function check_opts(array $opts)
    {
        $opts = array_merge($this->default_opts, $opts);
        if ($opts['clear_error_bag']) {
            $this->clear_error_bag();
        }
        return $opts;
    }

    protected function update_error_bag(array $errors)
    {
        $this->error_bag = array_merge_recursive($this->error_bag ?? [], $errors);
        return $this->error_bag;
    }
}
