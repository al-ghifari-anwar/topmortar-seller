<?php

class CartDetail extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MProduk');
        $this->load->model('MCart');
        $this->load->model('MCartDetail');
        $this->load->model('MVoucher');
    }

    public function create()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $id_cart = $post['id_cart'];
        $id_produk = $post['id_produk'];
        $qty_cart_detail = $post['qty_cart_detail'];

        $produk = $this->MProduk->getById($id_produk);

        $cartDetail = $this->MCartDetail->getByIdCartAndIdProduct($id_cart, $id_produk);

        if ($produk) {
            if ($cartDetail) {
                // Update qty
                $cartDetailData = [
                    'id_cart' => $id_cart,
                    'id_produk' => $id_produk,
                    'qty_cart_detail' => $qty_cart_detail,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ];

                $save = $this->MCartDetail->update($cartDetail['id_cart_detail'], $cartDetailData);

                if ($save) {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Produk ditambahkan',
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Terjadi kesalahan, harap coba lagi',
                    ];

                    $this->output->set_output(json_encode($result));
                }
            } else {
                // Insert New
                $cartDetailData = [
                    'id_cart' => $id_cart,
                    'id_produk' => $id_produk,
                    'qty_cart_detail' => $qty_cart_detail,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ];

                $save = $this->MCartDetail->create($cartDetailData);

                if ($save) {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Produk ditambahkan',
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Terjadi kesalahan, harap coba lagi',
                    ];

                    $this->output->set_output(json_encode($result));
                }
            }
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Produk tidak ditemukan',
            ];

            $this->output->set_output(json_encode($result));
        }
    }

    public function applyVoucher()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_cart = $post['id_cart'];
            $id_vouchers = $post['id_vouchers'];
            $id_products = $post['id_products'];

            // Update Voucher
            foreach ($id_vouchers as $id_voucher) {
                $voucherData = [
                    'is_used' => 1,
                    'used_date' => date('Y-m-d H:i:s'),
                ];

                $this->MVoucher->update($id_voucher, $voucherData);
            }

            // Insert Product
            foreach ($id_products as $id_product) {
                $cartDetailData = [
                    'id_cart' => $id_cart,
                    'id_produk' => $id_product['id'],
                    'qty_cart_detail' => $id_product['qty'],
                    'is_bonus' => 1,
                    'id_vouchers' => implode(",", $id_vouchers),
                ];

                $this->MCartDetail->create($cartDetailData);
            }

            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Berhasil menggunakan voucher'
            ];

            return $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not Found'
            ];

            return $this->output->set_output(json_encode($result));
        }
    }

    public function delete()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $save = $this->MCartDetail->delete($post['id_cart_detail']);

        if ($save) {
            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Produk dihapus dari keranjang',
            ];

            $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Terjadi kesalahan, harap coba lagi',
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
