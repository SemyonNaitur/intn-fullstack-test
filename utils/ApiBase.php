<?php

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
        $this->response = new ApiResponse();
    }

    public function run()
    {
        if (empty($this->input['method'])) {
            $this->response->status = 'EMPTY_METHOD';
            $this->response->message = 'Empty "method" param.';
        } else {
            $method = $this->input['method'];
            if (!method_exists($this, $method)) {
                $this->response->status = 'UNSUPORTED_METHOD';
                $this->response->message = "Unsuported method: $method.";
            } else {
                $this->$method($this->input['params'] ?? [], $this->response);
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

    protected function success($data = null, $msg = '', ApiResponse $resp = null)
    {
        $resp ??= $this->response;
        $resp->status = 'OK';
        $resp->message = $msg;
        $resp->data = $data;
    }

    protected function error($msg = '', $status = '', ApiResponse $resp = null)
    {
        $resp ??= $this->response;
        $resp->status = (empty($status)) ? 'ERR.' : $status;
        $resp->message = $msg;
    }

    /**
     * @return array - deeply merged error bag
     */
    protected function validation_fail(array $error_bag, $msg = '', ApiResponse $resp = null)
    {
        $resp ??= $this->response;
        $resp->status = 'VALIDATION_FAIL';
        $resp->message = (empty($msg)) ? 'Validation failed.' : $msg;
        $resp->data['errors'] = array_merge_recursive(($resp->data['errors'] ?? []), $error_bag);
        return $resp->data['errors'];
    }

    protected function output()
    {
        if ($this->input['raw_response'] ?? 0) {
            $json = json_encode($this->response->data, JSON_PRETTY_PRINT);
        } else {
            $json = json_encode($this->response);
        }
        header('Content-type: application/json');
        $resp = ($this->input['raw_response'] ?? 0) ? $this->response->data : $this->response;
        echo json_encode($resp);
        die;
    }
}
