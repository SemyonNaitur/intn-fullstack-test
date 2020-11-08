<?php

namespace System\Core;

use System\Libraries\Db;

abstract class Model
{
    protected Loader $load;
    protected Db $db;

    protected $table = '';
    protected $fields = [];
    protected $columns = [];

    public function init()
    {
        $this->initFields();
    }

    private function initFields()
    {
        $cols = [];
        foreach ($this->fields as $fld => &$params) {
            $col = $params['column'] ?? $fld;
            $params['column'] = $col;
            $cols[$col] = $fld;
        }
        $this->columns = $cols;
    }

    /**
     * Converts field name to db column name
     * 
     * @return string|bool
     */
    protected function fieldToColumn(string $field)
    {
        return $this->fields[$field]['column'] ?? false;
    }

    /**
     * Converts db column name to field name
     * 
     * @return string|bool
     */
    protected function columnToField(string $column)
    {
        return $this->columns[$column] ?? false;
    }

    /**
     * Converts an array of field names to a string for select statements.
     * format: table_name.column_name AS fieldName
     * 
     * @param 	array 	$fields
     * @return 	string
     */
    protected function colsAsFields(array $fields = null): string
    {
        $fields ??= array_keys($this->fields);
        $fields = array_map(
            function ($field) {
                if (!($col = $this->fieldToColumn($field))) {
                    throw new \Exception("Unknown field: $field");
                }
                return "$this->table.$col AS $field";
            },
            $fields
        );
        return implode(', ', $fields);
    }

    /**
     * Checks if the value axists in the table
     * 
     * @param 	string|number 	$value
     * @param 	string 			$field field or column name
     * @return 	bool
     */
    public function isUnique($value, string $field): bool
    {
        $col = $this->fieldToColumn($field) ?: $field;
        return $this->db->isUnique($value, $col, $this->table);
    }

    /**
     * Removes unwanted fields from given record.
     * 
     * @param 	array 	$record
     * @param 	array 	$allowed optional, wanted fields list
     * @return 	array 	new filtered array
     */
    public function filterFields(array $record, $allowed = []): array
    {
        $allowed = $allowed ?: array_keys($this->fields);
        return array_filter(
            $record,
            function ($k) use ($allowed) {
                return in_array($k, $allowed);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
