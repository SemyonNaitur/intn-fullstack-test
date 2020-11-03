<?php

namespace System;

abstract class ApiBase
{
    protected array $input;
    protected ApiResponse $response;

    public function __construct(array $input)
    {
        $this->input = $input;
        $this->setResponse(new ApiResponse());
    }

    public function run(): void
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

    /**
     * Returns initial input array.
     * 
     * @return array
     */
    public function getInput(): array
    {
        return $this->input;
    }

    /**
     * Replaces default response instance
     * 
     * @param   ApiResponse $response
     * @return  void
     */
    public function setResponse(ApiResponse $response): void
    {
        $this->response = $response;
    }

    /**
     * Returns the response instance.
     * 
     * @return ApiResponse
     */
    public function getResponse(): ApiResponse
    {
        return $this->response;
    }

    /**
     * Checks if each parameter name from $required is set in $params and return a list of missing names.
     * 
     * @param   array       $params
     * @param   string[]    $required
     * @return  string[]
     */
    public function checkMissing(array $params, array $required): array
    {
        return array_filter($required, function ($v) use ($params) {
            return !isset($params[$v]);
        });
    }

    /**
     * Outputs and optionally terminates the request with OK status
     * 
     * @param   mixed   $data
     * @param   string  $message
     * @param   bool    $continue
     * @return  void
     */
    protected function success($data = null, $message = '', $continue = false): void
    {
        $resp = $this->response;
        $resp->status = 'OK';
        $resp->message = $message;
        $resp->data = $data;
        if (!$continue) $this->output();
    }

    /**
     * Outputs and optionally terminates the request with a status different from OK
     * 
     * @param   mixed   $data
     * @param   string  $message
     * @param   bool    $continue
     * @return  void
     */
    protected function error($message = '', $status = '', $continue = false): void
    {
        $resp = $this->response;
        $resp->status = (empty($status) || $status === 'OK') ? 'ERROR' : $status;
        $resp->message = $message;
        if (!$continue) $this->output();
    }

    /**
     * Gets a list of missing parameters list, generates a message and calls error().
     * For *non user* input.
     * 
     * @param   array   $names
     * @param   bool    $continue
     * @return  void
     */
    protected function paramsError(array $names, $continue = false)
    {
        $msg = 'Missing or invalid params: ' . implode(', ', $names) . '.';
        $this->error($msg, 'PARAMS_ERR', $continue);
    }

    /**
     * For *user* input.
     * 
     * @param   array       $error_bag
     * @param   string      $msg
     * @param   bool        $continue
     * @return  array|void  deeply merged error bag if continue = true
     */
    protected function validationFail(array $error_bag, $continue = false)
    {
        $resp = $this->response;
        $resp->status = 'VALIDATION_FAIL';
        $resp->message = 'Validation failed.';
        $resp->data['errors'] = array_merge_recursive(($resp->data['errors'] ?? []), $error_bag);
        return ($continue) ? $resp->data['errors'] : $this->output();
    }

    final protected function output(ApiResponse $resp = null): void
    {
        header('Content-type: application/json');
        $resp ??= $this->response;
        $resp = ($this->input['raw_response'] ?? 0) ? $resp->data : $resp;
        echo json_encode($resp);
        die;
    }
}
