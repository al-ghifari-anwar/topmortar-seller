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

            $contact = $this->MContact->getById($id_contact);

            $getVoucherTukang = $this->MVoucherTukang->getByIdContact($id_contact);

            if ($getVoucherTukang == null) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'quota' => $contact['quota_priority'],
                    'msg' => 'Tidak ada data'
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Sukses mengambil data penukaran',
                    'quota' => $contact['quota_priority'],
                    'data' => $getVoucherTukang
                ];

                return $this->output->set_output(json_encode($result));
            }
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not Found'
            ];

            return $this->output->set_output(json_encode($result));
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
        $topseller_active = $getContact['topseller_active'];

        $getVouchers = $this->MVoucherTukang->getByIdContact($id_contact);
        $quotaContact = $getContact['quota_priority'];
        $countVoucher = count($getVouchers);

        $currentQuota = $quotaContact - $countVoucher;

        if ($currentQuota <= 0) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Quota penukaran telah habis, hubungi Top Mortar Official di 087826210888 untuk informasi lebih lanjut'
            ];

            return $this->output->set_output(json_encode($result));
        }

        if ($topseller_active == 0) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Toko anda tidak aktif, hubungi Top Mortar Official di 087826210888 untuk informasi lebih lanjut'
            ];

            return $this->output->set_output(json_encode($result));
        }

        if ($getContact['store_status'] == 'blacklist') {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Toko anda tidak memenuhi syarat, hubungi Top Mortar Official di 087826210888 untuk informasi lebih lanjut'
            ];

            return $this->output->set_output(json_encode($result));
        } else {
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

                return $this->output->set_output(json_encode($result));
            } else {
                if ($getVoucher['type_voucher'] == 'priority' || $getVoucher['type_voucher'] == 'tokopromo') {
                    if ($getVoucher['id_contact'] != $id_contact) {
                        $result = [
                            'code' => 400,
                            'status' => 'failed',
                            'msg' => 'Voucher anda tidak dapat diclaim di toko ini'
                        ];

                        return $this->output->set_output(json_encode($result));
                    }
                }

                if ($getVoucher['is_claimed'] == 1) {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'QR voucher sudah pernah diclaim'
                    ];

                    return $this->output->set_output(json_encode($result));
                } else {
                    $checkStoreClaim = $this->MVoucherTukang->checkStoreClaim($id_md5, $id_contact);

                    if ($checkStoreClaim != null) {
                        $result = [
                            'code' => 400,
                            'status' => 'failed',
                            'msg' => 'QR voucher sudah pernah diclaim di toko ini'
                        ];

                        return $this->output->set_output(json_encode($result));
                    } else {
                        if ($getVoucher['exp_at'] < date("Y-m-d")) {
                            $result = [
                                'code' => 400,
                                'status' => 'failed',
                                'msg' => 'QR voucher sudah expired'
                            ];

                            return $this->output->set_output(json_encode($result));
                        } else {
                            // Success
                            $getRekeningToko = $this->MRekeningToko->getByIdContact($id_contact);
                            $to_name = $nama_tukang;
                            $to_account = $getRekeningToko['to_account'];
                            $is_bca = $getRekeningToko['is_bca'];
                            $nama_bank = $getRekeningToko['nama_bank'];
                            $swift_code = $getRekeningToko['swift_bank'];

                            if ($is_bca == 1) {
                                // $distributor = $this->db->get_where('tb_distributor', ['id_distributor' => 1])->row_array();
                                $api_key = '7d6cf89089723eb4e4727cec99f1962f';
                                // !! TF intrabank
                                $amount = 10000;
                                $remark = "Auto Trf Vc - " . substr($to_name, 0, 6);

                                $curl = curl_init();

                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'https://central.topmortarindonesia.com/intra',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',
                                    CURLOPT_POSTFIELDS => array('amount' => $amount, 'toName' => $to_name, 'toAccount' => $to_account, 'remark' => $remark),
                                    CURLOPT_HTTPHEADER => array(
                                        'x-api-key: ' . $api_key,
                                        'x-timestamp: ' . date("Y-m-d H:i:s")
                                    ),
                                ));

                                $response = curl_exec($curl);

                                curl_close($curl);

                                $res = json_decode($response, true);

                                if ($res['code'] != 200) {

                                    $result = [
                                        'code' => 400,
                                        'status' => 'failed',
                                        'msg' => 'Proses claim gagal, harap pastikan nomor rekening anda benar',
                                        'detail' => $res['detail']
                                    ];

                                    return $this->output->set_output(json_encode($result));
                                } else {
                                    $resData = $res['data'];

                                    $statusIntra = $resData['responseMessage'] == 'Successful' ? 'success' : 'failed';

                                    $logData = [
                                        'source_account' => '8881051362',
                                        'to_account' => $to_account,
                                        'amount_log_bca' => $amount,
                                        'status_log_bca' => $statusIntra,
                                        'ref_log_bca' => $resData['referenceNo'],
                                        'desc_log_bca' => $resData['responseMessage'],
                                        'created_at' => date("Y-m-d H:i:s"),
                                        'updated_at' => date("Y-m-d H:i:s"),
                                    ];

                                    $saveLog = $this->db->insert('tb_log_bca', $logData);

                                    if ($statusIntra != 'success') {
                                        $result = [
                                            'code' => 400,
                                            'status' => 'failed',
                                            'msg' => 'Transaksi gagal, harap coba lagi',
                                            'detail' => $res
                                        ];

                                        return $this->output->set_output(json_encode($result));
                                    } else {
                                        // Claim
                                        $this->MVoucherTukang->claim($id_md5, $id_contact);

                                        // Send Message
                                        $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
                                        $integration_id = $getQontak['integration_id'];
                                        $wa_token = $getQontak['token'];
                                        // $template_id = '781b4601-fba6-4c69-81ad-164a680ecce7';
                                        $template_id = '7bf2d2a0-bdd5-4c70-ba9f-a9665f66a841';

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
                                            $template_id = '7bf2d2a0-bdd5-4c70-ba9f-a9665f66a841';

                                            $message = "Selamat anda telah mendapat potongan diskon 10.000. Program ini disponsori oleh Top Mortar Indonesia";
                                            $img_tukang = "https://seller.topmortarindonesia.com/assets/img/notif_tukang.png";

                                            if ($getVoucher['type_voucher'] == 'tokopromo') {
                                                $message = "Selamat anda telah mendapat potongan diskon 5.000. Program ini disponsori oleh Top Mortar Indonesia";
                                                $img_tukang = "https://seller.topmortarindonesia.com/assets/img/notif_tukang_tokopromo.png";
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
                                                    "header":{
                                                        "format":"IMAGE",
                                                        "params": [
                                                            {
                                                                "key":"url",
                                                                "value":"' . $img_tukang . '"
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
                                                // $this->MVoucherTukang->claim($id_md5, $id_contact);

                                                $result = [
                                                    'code' => 200,
                                                    'status' => 'ok',
                                                    'msg' => 'Claim voucher berhasil, dana telah masuk ke rekening / e-wallet anda'
                                                ];

                                                return $this->output->set_output(json_encode($result));
                                            }
                                        } else {
                                            $result = [
                                                'code' => 400,
                                                'status' => 'failed',
                                                'msg' => 'Failed',
                                                'detail' => $res
                                            ];

                                            return $this->output->set_output(json_encode($result));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
