<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Produk extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('MContact');
        $this->load->model('MProduk');
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function get()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['city'])) {
                $id_city = $_GET['city'];

                $this->output->set_content_type('application/json');

                $getProduk = $this->MProduk->getByIdCity($id_city);

                if ($getProduk == null) {
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
                        'msg' => 'Sukses mengambil data produk',
                        'data' => $getProduk,
                    ];

                    $this->output->set_output(json_encode($result));
                }
            } else {
                $this->output->set_content_type('application/json');

                $getProduk = $this->MProduk->get();

                if ($getProduk == null) {
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
                        'msg' => 'Sukses mengambil data produk',
                        'data' => $getProduk,
                    ];

                    $this->output->set_output(json_encode($result));
                }
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
