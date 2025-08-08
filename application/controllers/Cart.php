<?php

class Cart extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MCart');
        $this->load->model('MCartDetail');
        $this->load->model('MContact');
        $this->load->model('MApporder');
        $this->load->model('MApporderDetail');
        $this->load->model('MSuratJalan');
        $this->load->model('MDetailSuratJalan');
        $this->load->model('MDiscountApp');
        $this->load->model('MPromo');
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

                $discountApp = $this->MDiscountApp->get();
                $discount_app = $discountApp['amount_discount_app'];

                $subtotal_price = 0;
                $total_qty_cart_detail = 0;

                if ($cartDetails) {
                    foreach ($cartDetails as $cartDetail) {
                        $subtotal_price += $cartDetail['harga_produk'] * $cartDetail['qty_cart_detail'];
                        $total_qty_cart_detail += $cartDetail['qty_cart_detail'];
                    }

                    $cart['subtotal_price'] = $subtotal_price . "";
                    $cart['discount_app'] = $discount_app . "";
                    $cart['total_discount_app'] = $discount_app * $total_qty_cart_detail . "";
                    $cart['total_price'] = $subtotal_price - ($discount_app * $cartDetail['qty_cart_detail']) . "";
                    $cart['details'] = $cartDetails;
                } else {
                    $cart['subtotal_price'] = $subtotal_price . "";
                    $cart['discount_app'] = 0 . "";
                    $cart['total_discount_app'] = 0 . "";
                    $cart['total_price'] = 0 . "";
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

    public function checkout()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_contact = $post['id_contact'];
            $id_cart = $post['id_cart'];

            $discountApp = $this->MDiscountApp->get();
            $discount_app = $discountApp['amount_discount_app'];

            $contact = $this->MContact->getById($id_contact);

            $cartDetails = $this->MCartDetail->getByIdCart($id_cart);

            $subtotal_apporder = 0;
            $discount_apporder = 0;

            foreach ($cartDetails as $cartDetail) {
                $subtotal_apporder += $cartDetail['harga_produk'] * $cartDetail['qty_cart_detail'];
                $discount_apporder += $discount_app * $cartDetail['qty_cart_detail'];
            }

            $apporderData = [
                'id_cart' => $id_cart,
                'id_contact' => $id_contact,
                'subtotal_apporder' => $subtotal_apporder,
                'discount_apporder' => $discount_apporder,
                'total_apporder' => $subtotal_apporder - $discount_apporder,
            ];

            $saveApporder = $this->MApporder->create($apporderData);

            if ($saveApporder) {
                $id_apporder = $this->db->insert_id();

                $promo = $this->MPromo->getById($contact['id_promo']);

                foreach ($cartDetails as $cartDetail) {
                    $apporderDetailData = [
                        'id_apporder' => $id_apporder,
                        'id_produk' => $cartDetail['id_produk'],
                        'img_produk' => '',
                        'name_produk' => $cartDetail['nama_produk'],
                        'price_produk' => $cartDetail['harga_produk'],
                        'qty_apporder_detail' => $cartDetail['qty_cart_detail'],
                        'total_apporder_detail' => $cartDetail['qty_cart_detail'] * $cartDetail['harga_produk'],
                    ];

                    $saveApporderDetail = $this->MApporderDetail->create($apporderDetailData);

                    // Calculate Bonus
                    if ($contact['id_promo'] != 0) {
                        $qty_apporder_detail = $cartDetail['qty_cart_detail'];
                        $kelipatan_promo = $promo['kelipatan_promo'];

                        $multiplier = $qty_apporder_detail / $kelipatan_promo;

                        if (floor($multiplier) > 0) {
                            if ($contact['id_distributor'] != '6') {
                                $apporderBonusDetailData = [
                                    'id_apporder' => $id_apporder,
                                    'id_produk' => $cartDetail['id_produk'],
                                    'img_produk' => '',
                                    'name_produk' => $cartDetail['nama_produk'],
                                    'is_bonus' => 1,
                                    'price_produk' => $cartDetail['harga_produk'],
                                    'qty_apporder_detail' => $cartDetail['qty_cart_detail'],
                                    'total_apporder_detail' => 0,
                                ];

                                $saveApporderBonusDetail = $this->MApporderDetail->create($apporderBonusDetailData);
                            }
                        }
                    }
                }

                $cartData = [
                    'status_cart' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $this->MCart->update($cartData, $id_cart);

                if ($contact['reputation'] != 'good') {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Pesanan diterima, menunggu konfirmasi'
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    $minimumScore = 50;
                    // Get Score from API
                    // Get Score
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://order.topmortarindonesia.com/scoring/combine/' . $id_contact,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_HTTPHEADER => array(
                            'Cookie: ci_session=2scmao9aquusdrn7rm2i7vkrifkamkld'
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $res = json_decode($response, true);

                    if ($res['total'] < $minimumScore) {
                        $result = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'Pesanan diterima, menunggu konfirmasi',
                            'data' => [
                                'score' => $res
                            ]
                        ];

                        $this->output->set_output(json_encode($result));
                    } else {

                        $this->insertSJ($id_apporder, $id_contact);
                    }
                }
            } else {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Terjadi kesalahan'
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

    public function insertSJ($id_apporder, $id_contact)
    {
        $contact = $this->MContact->getById($id_contact);

        $id_distributor = $contact['id_distributor'];
        $id_city = $contact['id_city'];

        $apporder = $this->MApporder->getById($id_apporder);

        $approderDetails = $this->MApporderDetail->getByIdApporder($apporder['id_apporder']);

        $id_courier = 18;

        $suratJalanData = [
            'id_apporder' => $id_apporder,
            'no_surat_jalan' => "DO-" . rand(10000000, 99999999),
            'id_contact' => $id_contact,
            'dalivery_date' => date("Y-m-d H:i:s"),
            'order_number' => 0,
            'ship_to_name' => $contact['nama'],
            'ship_to_address' => $contact['address'],
            'ship_to_phone' => $contact['nomorhp'],
            'id_courier' => $id_courier,
            'id_kendaraan' => 2,
            'is_finished' => 1,
            'is_cod' => 0,
        ];

        $saveSuratJalan = $this->MSuratJalan->create($suratJalanData);

        if ($saveSuratJalan) {
            $id_surat_jalan = $this->db->insert_id();

            $suratJalan = $this->MSuratJalan->getById($id_surat_jalan);

            foreach ($approderDetails as $approderDetail) {
                $detailSuratJalanData = [
                    'id_surat_jalan' => $id_surat_jalan,
                    'id_produk' => $approderDetail['id_produk'],
                    'price' => $approderDetail['price_produk'],
                    'qty_produk' => $approderDetail['qty_apporder_detail'],
                    'amount' => $approderDetail['is_bonus'] == 0 ? $approderDetail['total_apporder_detail'] : 0,
                    'is_bonus' => $approderDetail['is_bonus'],
                    'no_voucher' => '',
                ];

                $saveDetailSuratJalan = $this->MDetailSuratJalan->create($detailSuratJalanData);
            }

            // Send notif kurir
            $qontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
            $integration_id = $qontak['integration_id'];
            $wa_token = $qontak['token'];
            $template_id = '32b18403-e0ee-4cfc-9e2e-b28b95f24e37';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "to_number": "' . $suratJalan['phone_user'] . '",
                    "to_name": "' . $suratJalan['full_name'] . '",
                    "message_template_id": "' . $template_id . '",
                    "channel_integration_id": "' . $integration_id . '",
                    "language": {
                        "code": "id"
                    },
                    "parameters": {
                        "body": [
                        {
                            "key": "1",
                            "value": "nama",
                            "value_text": "' . $suratJalan['full_name'] . '"
                        },
                        {
                            "key": "2",
                            "value": "store",
                            "value_text": "' . $suratJalan['nama'] . '"
                        },
                        {
                            "key": "3",
                            "value": "address",
                            "value_text": "' . trim(preg_replace('/\s+/', ' ', $suratJalan['address'])) . ', ' . $suratJalan['nama_city'] . '"
                        },
                        {
                            "key": "4",
                            "value": "no_surat",
                            "value_text": "' . $suratJalan['no_surat_jalan'] . '"
                        }
                        ]
                    }
                    }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $wa_token,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $res = json_decode($response, true);

            if ($res['status'] == 'success') {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Order diterima'
                ];

                $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Order diterima, menunggu konfirmasi'
                ];

                $this->output->set_output(json_encode($result));
            }
        } else {
            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Order diterima, menunggu konfirmasi'
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
