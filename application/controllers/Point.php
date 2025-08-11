<?php

class Point extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPoint');
    }

    public function getTotal()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_contact = $_GET['id_contact'];

            $point = $this->MPoint->getTotalPointByIdContact($id_contact);

            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $point
            ];

            $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
