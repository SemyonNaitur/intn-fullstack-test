<?php

class AjaxResponse
{
    public $data = null;

    public function __construct($status = 'OK', $message = '')
    {
        $this->status = $status;
        $this->message = $message;
    }
}

abstract class AjaxUtil
{
    protected array $input;
    protected AjaxResponse $response;

    public function __construct(array $input)
    {
        $this->input = (is_array($input)) ? $input : [];
        $this->response = new AjaxResponse();
    }

    public function run()
    {
        if (empty($this->input['method'])) {
            $this->response->status = 'EMPTY_METHOD';
            $this->response->message = "Empty 'method' param.";
        } else {
            $method = $this->input['method'];
            if (!method_exists($this, $method)) {
                $this->response->status = 'UNSUPORTED_METHOD';
                $this->response->message = "Unsuported method: $method.";
            } else {
                $this->$method($this->input['params'], $this->response);
            }
        }
        $this->output();
    }

    public function get_input(): array
    {
        return $this->input;
    }

    public function set_response(AjaxResponse $resp)
    {
        $this->response = $resp;
    }

    public function get_response(): AjaxResponse
    {
        return $this->response;
    }

    protected function validation_fail(string $msg, array $error_bag, AjaxResponse $resp = null)
    {
        $resp ??= $this->response;
        $resp->status = 'VALIDATION_FAIL';
        $resp->message = $msg;
        $resp->data['errors'] = array_merge(($resp->data['errors'] ?? []), $error_bag);
    }

    protected function output()
    {
        $resp = (($this->input['raw_response'] ?? 0) == 1) ? $this->response->data : $this->response;
        echo json_encode($resp);
        die;
    }
}
