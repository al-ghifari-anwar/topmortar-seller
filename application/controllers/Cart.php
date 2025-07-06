<?php

class Cart extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MCart');
        $this->load->model('MCartDetail');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_contact = $_GET['id_contact'];

            $cart = $this->MCart->getByIdContact($id_contact);

            if (!$cart) {
                $cartData = [
                    'id_contact' => $id_contact,
                    'status_cart' => 'active',
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ];

                $save = $this->MCart->create($cartData);

                if (!$save) {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Failed creating cart data'
                    ];

                    $this->output->set_output(json_encode($result));
                } else {

                    $cart = $this->MCart->getByIdContact($id_contact);

                    $cartDetails = $this->MCartDetail->getByIdCart($cart['id_cart']);

                    $subtotal_price = 0;

                    if ($cartDetails) {
                        foreach ($cartDetails as $cartDetail) {
                            $subtotal_price += $cartDetail['harga_produk']  * $cartDetail['qty_cart_detail'];
                        }

                        $cart['subtotal_price'] = $subtotal_price . "";
                        $cart['details'] = $cartDetails;
                    } else {
                        $cart['subtotal_price'] = $subtotal_price . "";
                        $cart['details'] = $cartDetails;
                    }


                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Cart found',
                        'data' => $cart,
                    ];

                    $this->output->set_output(json_encode($result));
                }
            } else {
                $cart = $this->MCart->getByIdContact($id_contact);

                $cartDetails = $this->MCartDetail->getByIdCart($cart['id_cart']);

                $subtotal_price = 0;

                if ($cartDetails) {
                    foreach ($cartDetails as $cartDetail) {
                        $subtotal_price += $cartDetail['harga_produk'] * $cartDetail['qty_cart_detail'];
                    }

                    $cart['subtotal_price'] = $subtotal_price . "";
                    $cart['details'] = $cartDetails;
                } else {
                    $cart['subtotal_price'] = $subtotal_price . "";
                    $cart['details'] = $cartDetails;
                }

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Cart found',
                    'data' => $cart,
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
