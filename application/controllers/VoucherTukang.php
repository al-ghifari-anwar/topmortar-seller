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
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function getByIdContact($id_contact)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $this->output->set_content_type('application/json');

            $getVoucherTukang = $this->MVoucherTukang->getByIdContact($id_contact);

            if ($getVoucherTukang == null) {
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
                    'data' => $getVoucherTukang
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

    public function claim()
    {
        $this->output->set_content_type('application/json');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $id_contact = $post['id_contact'];
        $id_md5 = $post['id_md5'];

        $this->db->join('tb_city', 'tb_city.id_city = tb_contact.id_city');
        $getContact = $this->db->get_where('tb_contact', ['id_contact' => $id_contact])->row_array();
        $nomorhp_contact = $getContact['nomorhp'];
        $nama_contact = $getContact['nama'];
        $id_distributor = $getContact['id_distributor'];

        $getVoucher = $this->MVoucherTukang->getByIdMd5($id_md5);
        $id_tukang = $getVoucher['id_tukang'];

        $getTukang = $this->db->get_where('tb_tukang', ['id_tukang' => $id_tukang])->row_array();
        $nomorhp_tukang = $getTukang['nomorhp'];
        $nama_tukang = $getTukang['nama'];

        if (!$getVoucher) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Voucher tidak ditemukan'
            ];

            $this->output->set_output(json_encode($result));
        } else {
            if ($getVoucher['is_claimed'] == 1) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'QR voucher sudah pernah diclaim'
                ];

                $this->output->set_output(json_encode($result));
            } else {
                if ($getVoucher['exp_at'] < date("Y-m-d")) {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'QR voucher sudah expired'
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    // Success
                    $getRekeningToko = $this->MRekeningToko->getByIdContact($id_contact);
                    $to_name = $nama_tukang;
                    $to_account = $getRekeningToko['to_account'];
                    $is_bca = $getRekeningToko['is_bca'];
                    $nama_bank = $getRekeningToko['nama_bank'];
                    $swift_code = $getRekeningToko['swift_bank'];

                    if ($is_bca == 1) {
                        $to_name = str_replace(" ", "%20", $to_name);
                        // TF intrabank
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://apibca.topmortarindonesia.com/snapIntrabankVctukang.php?to=' . $to_account . '&to_name=' . $to_name,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $res = json_decode($response, true);

                        if ($res['status'] != 'ok') {
                            $result = [
                                'code' => 400,
                                'status' => 'failed',
                                'msg' => 'Proses claim gagal'
                            ];

                            $this->output->set_output(json_encode($result));
                        } else {
                            // Send Message
                            $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
                            $integration_id = $getQontak['integration_id'];
                            $wa_token = $getQontak['token'];
                            $template_id = '781b4601-fba6-4c69-81ad-164a680ecce7';

                            $message = "Transaksi claim voucher atas nama " . $nama_tukang . " Berhasil. Dana telah ditransfer ke rekening anda. Silahkan cek mutasi anda.";

                            $curl = curl_init();

                            curl_setopt_array(
                                $curl,
                                array(
                                    CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',
                                    CURLOPT_POSTFIELDS => '{
                                        "to_number": "' . $nomorhp_contact . '",
                                        "to_name": "' . $nama_contact . '",
                                        "message_template_id": "' . $template_id . '",
                                        "channel_integration_id": "' . $integration_id . '",
                                        "language": {
                                            "code": "id"
                                        },
                                        "parameters": {
                                            "header":{
                                                "format":"IMAGE",
                                                "params": [
                                                    {
                                                        "key":"url",
                                                        "value":"https://seller.topmortarindonesia.com/assets/img/notif_toko.png"
                                                    },
                                                    {
                                                        "key":"filename",
                                                        "value":"qrtukang.png"
                                                    }
                                                ]
                                            },
                                            "body": [
                                            {
                                                "key": "1",
                                                "value": "nama",
                                                "value_text": "' . $message . '"
                                            }
                                            ]
                                        }
                                    }',
                                    CURLOPT_HTTPHEADER => array(
                                        'Authorization: Bearer ' . $wa_token,
                                        'Content-Type: application/json'
                                    ),
                                )
                            );

                            $response = curl_exec($curl);

                            curl_close($curl);

                            $res = json_decode($response, true);

                            $status = $res['status'];

                            if ($status == 'success') {
                                // Send Message Tukang
                                $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
                                $integration_id = $getQontak['integration_id'];
                                $wa_token = $getQontak['token'];
                                $template_id = '781b4601-fba6-4c69-81ad-164a680ecce7';

                                $message = "Selamat anda telah mendapat potongan diskon 10.000. Program ini disponsori oleh Top Mortar Indonesia";

                                $curl = curl_init();

                                curl_setopt_array(
                                    $curl,
                                    array(
                                        CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => '',
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 0,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => 'POST',
                                        CURLOPT_POSTFIELDS => '{
                                        "to_number": "' . $nomorhp_tukang . '",
                                        "to_name": "' . $nama_tukang . '",
                                        "message_template_id": "' . $template_id . '",
                                        "channel_integration_id": "' . $integration_id . '",
                                        "language": {
                                            "code": "id"
                                        },
                                        "parameters": {
                                            "header":{
                                                "format":"IMAGE",
                                                "params": [
                                                    {
                                                        "key":"url",
                                                        "value":"https://seller.topmortarindonesia.com/assets/img/notif_tukang.png"
                                                    },
                                                    {
                                                        "key":"filename",
                                                        "value":"qrtukang.png"
                                                    }
                                                ]
                                            },
                                            "body": [
                                            {
                                                "key": "1",
                                                "value": "nama",
                                                "value_text": "' . $message . '"
                                            }
                                            ]
                                        }
                                    }',
                                        CURLOPT_HTTPHEADER => array(
                                            'Authorization: Bearer ' . $wa_token,
                                            'Content-Type: application/json'
                                        ),
                                    )
                                );

                                $response = curl_exec($curl);

                                curl_close($curl);

                                $res = json_decode($response, true);

                                $status = $res['status'];

                                if ($status == 'success') {
                                    $this->MVoucherTukang->claim($id_md5, $id_contact);

                                    $result = [
                                        'code' => 200,
                                        'status' => 'ok',
                                        'msg' => 'Claim voucher berhasil, dana telah masuk ke rekening / e-wallet anda'
                                    ];

                                    $this->output->set_output(json_encode($result));
                                }
                            } else {
                                $result = [
                                    'code' => 400,
                                    'status' => 'failed',
                                    'msg' => 'Failed',
                                    'detail' => $res
                                ];

                                $this->output->set_output(json_encode($result));
                            }
                        }
                    } else {
                        $to_name = str_replace(" ", "%20", $to_name);

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://apibca.topmortarindonesia.com/snapInterbankVctukang.php?to=$to_account&to_name=$to_name&bank_code=$swift_code",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $res = json_decode($response, true);

                        if ($res['status'] != 'ok') {
                            $result = [
                                'code' => 400,
                                'status' => 'failed',
                                'msg' => 'Proses claim gagal',
                                'detail' => $res['detail']
                            ];

                            $this->output->set_output(json_encode($result));
                        } else {
                            $this->MVoucherTukang->claim($id_md5, $id_contact);
                            // Send Message
                            $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
                            $integration_id = $getQontak['integration_id'];
                            $wa_token = $getQontak['token'];
                            $template_id = '9ac4e6a5-0a71-4d00-981b-6cf05e5637da';

                            $message = "Dana telah ditransfer ke rekening anda. Silahkan cek mutasi anda.";

                            $curl = curl_init();

                            curl_setopt_array(
                                $curl,
                                array(
                                    CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',
                                    CURLOPT_POSTFIELDS => '{
                                        "to_number": "' . $nomorhp_contact . '",
                                        "to_name": "' . $nama_contact . '",
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
                                                "value_text": "' . $nama_contact . '"
                                            },
                                            {
                                                "key": "2",
                                                "value": "message",
                                                "value_text": "' . $message . '"
                                            }
                                            ]
                                        }
                                    }',
                                    CURLOPT_HTTPHEADER => array(
                                        'Authorization: Bearer ' . $wa_token,
                                        'Content-Type: application/json'
                                    ),
                                )
                            );

                            $response = curl_exec($curl);

                            curl_close($curl);

                            $res = json_decode($response, true);

                            $status = $res['status'];

                            if ($status == 'success') {
                                // Send Message
                                $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
                                $integration_id = $getQontak['integration_id'];
                                $wa_token = $getQontak['token'];
                                $template_id = '9ac4e6a5-0a71-4d00-981b-6cf05e5637da';

                                $message = "Selamat anda telah mendapat potongan diskon 10.000. Program ini disponsori oleh Top Mortar Indonesia";

                                if ($getVoucher['type_voucher'] == 'tokopromo') {
                                    $message = "Selamat anda telah mendapat potongan diskon 5.000. Program ini disponsori oleh Top Mortar Indonesia";
                                }

                                $curl = curl_init();

                                curl_setopt_array(
                                    $curl,
                                    array(
                                        CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => '',
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 0,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => 'POST',
                                        CURLOPT_POSTFIELDS => '{
                                        "to_number": "' . $nomorhp_tukang . '",
                                        "to_name": "' . $nama_tukang . '",
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
                                                "value_text": "' . $nama_contact . '"
                                            },
                                            {
                                                "key": "2",
                                                "value": "message",
                                                "value_text": "' . $message . '"
                                            }
                                            ]
                                        }
                                    }',
                                        CURLOPT_HTTPHEADER => array(
                                            'Authorization: Bearer ' . $wa_token,
                                            'Content-Type: application/json'
                                        ),
                                    )
                                );

                                $response = curl_exec($curl);

                                curl_close($curl);

                                $res = json_decode($response, true);

                                $status = $res['status'];

                                if ($status == 'success') {

                                    $result = [
                                        'code' => 200,
                                        'status' => 'ok',
                                        'msg' => 'Claim voucher berhasil, dana telah masuk ke rekening / e-wallet anda'
                                    ];
                                }
                            }

                            $this->output->set_output(json_encode($result));
                        }
                    }
                }
            }
        }
    }
}
