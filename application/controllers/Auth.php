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
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function register()
    {
        $this->output->set_content_type('application/json');

        $getContact = $this->MContact->getByNomorhp();

        if ($getContact == null) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Nomor tidak terdaftar'
            ];

            $this->output->set_output(json_encode($result));
        } else {
            if ($getContact['store_status'] == 'blacklist') {
                $result = [
                    'code' => 400,
                    'status' => 'ok',
                    'msg' => 'Toko tidak memenuhi syarat',
                    'data' => $getContact
                ];

                $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Nomor sesuai',
                    'data' => $getContact
                ];

                $this->output->set_output(json_encode($result));
            }
        }
    }

    public function send_otp()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $id_distributor = $post['id_distributor'];
        $id_contact = $post['id_contact'];

        $createOtp = $this->MOtpToko->create();

        if (!$createOtp) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Gagal membuat otp'
            ];

            $this->output->set_output(json_encode($result));
        } else {
            // Get Created OTP
            $getOtp = $this->MOtpToko->getByIdContact($id_contact);
            // Get Data Toko
            $getContact = $this->MContact->getById($id_contact);
            // Send Message
            $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
            $integration_id = $getQontak['integration_id'];
            $wa_token = $getQontak['token'];
            $template_id = '9ac4e6a5-0a71-4d00-981b-6cf05e5637da';

            $message = "Gunakan kode OTP *" . $getOtp['otp'] . "* untuk melanjutkan proses pendaftaran akun. Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun.";

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
                    "to_number": "' . $getContact['nomorhp'] . '",
                    "to_name": "' . $getContact['nama'] . '",
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
                            "value_text": "' . $getContact['nama'] . '"
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
                    'msg' => 'OTP berhasil terkirim'
                ];

                $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'OTP gagal terkirim',
                    'detail' => $res
                ];

                $this->output->set_output(json_encode($result));
            }
        }
    }

    public function verify_otp()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $id_contact = $post['id_contact'];
        $otp = $post['otp'];

        $verify = $this->MOtpToko->getForVerify();

        if ($verify == null) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'OTP tidak ditemukan'
            ];

            $this->output->set_output(json_encode($result));
        } else {
            if ($verify['is_used'] == 1) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Kode OTP sudah terpakai, silahkan request OTP ulang'
                ];

                $this->output->set_output(json_encode($result));
            } else {
                if ($verify['exp_at'] < date("Y-m-d H:i:s")) {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Kode OTP telah expired, silahkan request OTP ulang'
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    $this->MOtpToko->updateUsed($id_contact);

                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Berhasil verifikasi kode OTP, silahkan membuat password baru'
                    ];

                    $this->output->set_output(json_encode($result));
                }
            }
        }
    }

    public function reset_password()
    {
        $this->output->set_content_type('application/json');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $id_contact = $post['id_contact'];

        $resetPassword = $this->MContact->resetPassword();

        if (!$resetPassword) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Gagal mengubah password, silahkan coba lagi'
            ];

            $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Berhasil membuat password baru, silahkan login'
            ];

            $this->output->set_output(json_encode($result));
        }
    }

    public function login()
    {
        $this->output->set_content_type('application/json');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $pass_contact = md5("TopSeller" . md5($post['pass_contact']));

        $getContact = $this->MContact->getByNomorhp();

        if (!$getContact) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Nomor tidak terdaftar pada sistem'
            ];

            $this->output->set_output(json_encode($result));
        } else {
            if ($getContact['store_status'] == 'blacklist') {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Toko anda tidak memenuhi syarat, hubungi Top Mortar Official di 087826210888 untuk informasi lebih lanjut'
                ];

                $this->output->set_output(json_encode($result));
            } else {
                if ($getContact['pass_contact'] != $pass_contact) {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Password salah, silahkan daftar terlebih dahulu atau coba lagi'
                    ];

                    $this->output->set_output(json_encode($result));
                } else if ($getContact['pass_contact'] == $pass_contact) {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Berhasil login, terimakasih ^_^',
                        'data' => $getContact
                    ];

                    $this->output->set_output(json_encode($result));
                }
            }
        }
    }
}
