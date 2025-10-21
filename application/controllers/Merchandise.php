<?php

class Merchandise extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MMerchandise');
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $merchandise = $this->MMerchandise->get();

            if ($merchandise == null) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Tidak ada data'
                ];

                $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Sukses mengambil data merchandise',
                    'data' => $merchandise,
                ];

                $this->output->set_output(json_encode($result));
            }
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not Found'
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
