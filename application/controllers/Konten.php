<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VoucherTukang extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('MContact');
        $this->load->model('MOtpToko');
        $this->load->model('MRekeningToko');
        $this->load->model('MBank');
        $this->load->model('MVoucherTukang');
        $this->load->model('MKonten');
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function get()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $this->output->set_content_type('application/json');

            $getKonten = $this->MKonten->get();

            if ($getKonten == null) {
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
                    'msg' => 'Sukses mengambil data penukaran',
                    'data' => $getKonten
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
