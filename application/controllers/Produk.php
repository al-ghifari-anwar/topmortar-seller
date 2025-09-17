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
        $this->load->model('MCartDetail');
        $this->load->model('MCart');
        $this->load->model('MMasterProduk');
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['id_contact'])) {
                $id_contact = $_GET['id_contact'];

                $contact = $this->MContact->getById($id_contact);

                $id_city = $contact['id_city'];

                $cart = $this->MCart->getByIdContact($contact['id_contact']);

                $produks = $this->MProduk->getByIdCity($id_city);

                $arrayData = array();
                foreach ($produks as $produk) {
                    $masterProduk = $this->MMasterProduk->getById($produk['id_master_produk']);
                    $cartDetail = $this->MCartDetail->getByIdCartAndIdProduct($cart['id_cart'], $produk['id_produk']);

                    $produk['img_produk'] = $masterProduk['img_master_produk'];

                    if ($cartDetail) {
                        $produk['cart'] = $cartDetail;
                    } else {
                        $produk['cart'] = array();
                    }

                    array_push($arrayData, $produk);
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
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'id_contact is required',
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
