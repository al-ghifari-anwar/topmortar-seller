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
        $this->load->model('MStok');
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

                $produks = $this->MProduk->getByIdCity($id_city);

                $arrayData = array();

                foreach ($produks as $produk) {
                    $id_produk = $produk['id_produk'];

                    $stokIn = $this->MStok->getStokIn($id_produk);
                    $stokOut = $this->MStok->getStokOut($id_produk);

                    $stok = $stokIn['jml_stok'] - $stokOut['jml_stok'];
                    $produk['stok'] = $stok;

                    $arrayData[] = $produk;
                }

                if ($produks == null) {
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
                        'data' => $arrayData,
                    ];

                    $this->output->set_output(json_encode($result));
                }
            } else {
                $this->output->set_content_type('application/json');

                $produks = $this->MProduk->get();

                $arrayData = array();

                foreach ($produks as $produk) {
                    $id_produk = $produk['id_produk'];

                    $stokIn = $this->MStok->getStokIn($id_produk);
                    $stokOut = $this->MStok->getStokOut($id_produk);

                    $stok = $stokIn['jml_stok'] - $stokOut['jml_stok'];
                    $produk['stok'] = $stok;

                    $arrayData[] = $produk;
                }

                if ($produks == null) {
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
                        'data' => $arrayData,
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
