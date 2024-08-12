<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('MContact');
        $this->load->model('MOtpToko');
        $this->load->model('MBank');
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function get()
    {
        $this->output->set_content_type('application/json');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $getBank = $this->MBank->get();

        if ($getBank == null) {
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
                'msg' => 'Sukses mengambil data',
                'data' => $getBank
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
