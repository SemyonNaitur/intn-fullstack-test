<?php

namespace System;

class ApiResponse
{
    public $data = null;

    public function __construct($status = '', $message = '')
    {
        $this->status = $status;
        $this->message = $message;
    }
}

abstract class ApiBase
{
    protected array $input;
    protected ApiResponse $response;

    public function __construct(array $input)
    {
        $this->input = $input;
        $this->set_response(new ApiResponse());
    }

    public function run()
    {
        if (empty($this->input['method'])) {
            $this->error('Empty "method" param.', 'EMPTY_METHOD');
        } else {
            $method = $this->input['method'];
            if (!method_exists($this, $method)) {
                $this->error("Unsuported method: $method.", 'UNSUPORTED_METHOD');
            } else {
                $this->$method($this->input['params'] ?? []);
            }
        }
        $this->output();
    }

    public function get_input(): array
    {
        return $this->input;
    }

    public function set_response(ApiResponse $resp)
    {
        $this->response = $resp;
    }

    public function get_response(): ApiResponse
    {
        return $this->response;
    }

    public function check_missing(array $params, array $required)
    {
        return array_filter($required, function ($v) use ($params) {
            return !isset($params[$v]);
        });
    }

    protected function success($data = null, $msg = '', $continue = false)
    {
        $resp = $this->response;
        $resp->status = 'OK';
        $resp->message = $msg;
        $resp->data = $data;
        $this->output();
    }

    protected function error($msg = '', $status = '', $continue = false)
    {
        $resp = $this->response;
        $resp->status = (empty($status)) ? 'ERR.' : $status;
        $resp->message = $msg;
        if (!$continue) $this->output();
    }

    /**
     * For *non user* input.
     * 
     * @param   array   $names
     * @return  void
     */
    protected function params_error(array $names, $continue = false)
    {
        $msg = 'Missing or invalid params: ' . implode(', ', $names) . '.';
        $this->error($msg, 'PARAMS_ERR', $continue);
    }

    /**
     * For *user* input.
     * 
     * @param   array       $error_bag
     * @param   string      $msg
     * @return  array|void  deeply merged error bag if continue = true
     */
    protected function validation_fail(array $error_bag, $continue = false)
    {
        $resp = $this->response;
        $resp->status = 'VALIDATION_FAIL';
        $resp->message = 'Validation failed.';
        $resp->data['errors'] = array_merge_recursive(($resp->data['errors'] ?? []), $error_bag);
        return ($continue) ? $resp->data['errors'] : $this->output();;
    }

    protected function output(ApiResponse $resp = null)
    {
        header('Content-type: application/json');
        $resp ??= $this->response;
        $resp = ($this->input['raw_response'] ?? 0) ? $resp->data : $resp;
        echo json_encode($resp);
        die;
    }
}
