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

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $nomorhp = $post['nomorhp'];
        $nomorhp = "62" . substr($nomorhp, 1);

        $getContact = $this->MContact->getByNomorhp();

        if ($getContact == null) {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Nomor tidak terdaftar'
            ];

            // Send Message
            // $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => 1])->row_array();
            // $integration_id = $getQontak['integration_id'];
            // $wa_token = $getQontak['token'];
            // $template_id = '9241bf86-ae94-4aa8-8975-551409af90b9';

            $message = "Ada pendaftar top seller baru dengan nomor " . $nomorhp . "";
            $nama_admin = 'Arie';
            $full_name = 'Automated Message';

            $haloai = $this->db->get_where('tb_haloai', ['id_distributor' => 1])->row_array();
            $wa_token = $haloai['token_haloai'];
            $business_id = $haloai['business_id_haloai'];
            $channel_id = $haloai['channel_id_haloai'];
            $template = 'info_meeting_baru';

            $haloaiPayload = [
                'activate_ai_after_send' => false,
                'channel_id' => $channel_id,
                'fallback_template_message' => $template,
                'fallback_template_variables' => [
                    $nama_admin,
                    trim(preg_replace('/\s+/', ' ', $message)),
                    $full_name,
                ],
                'phone_number' => "6287757904850",
                'text' => trim(preg_replace('/\s+/', ' ', $message)),
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.haloai.co.id/api/open/channel/whatsapp/v1/sendMessageByPhoneSync',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($haloaiPayload),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $wa_token,
                    'X-HaloAI-Business-Id: ' . $business_id,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $res = json_decode($response, true);

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
            // $getQontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
            // $integration_id = $getQontak['integration_id'];
            // $wa_token = $getQontak['token'];
            // $template_id = '9241bf86-ae94-4aa8-8975-551409af90b9';

            $message = "Gunakan kode OTP *" . $getOtp['otp'] . "* untuk melanjutkan proses pendaftaran akun. Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun.";

            $haloai = $this->db->get_where('tb_haloai', ['id_distributor' => 1])->row_array();
            $wa_token = $haloai['token_haloai'];
            $business_id = $haloai['business_id_haloai'];
            $channel_id = $haloai['channel_id_haloai'];
            $template = 'info_meeting_baru';

            $sender = "PT Top Mortar Indonesia";

            $haloaiPayload = [
                'activate_ai_after_send' => false,
                'channel_id' => $channel_id,
                'fallback_template_message' => $template,
                'fallback_template_variables' => [
                    $getContact['nama'],
                    trim(preg_replace('/\s+/', ' ', $message)),
                    $sender,
                ],
                'phone_number' => $getContact['nomorhp'],
                'text' => trim(preg_replace('/\s+/', ' ', $message)),
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.haloai.co.id/api/open/channel/whatsapp/v1/sendMessageByPhoneSync',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($haloaiPayload),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $wa_token,
                    'X-HaloAI-Business-Id: ' . $business_id,
                    'Content-Type: application/json'
                ),
            ));

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

                return $this->output->set_output(json_encode($result));
            } else {
                if ($getContact['is_deleted'] == 1) {
                    $result = [
                        'code' => 401,
                        'status' => 'failed',
                        'msg' => 'Akun anda tidak dapat ditemukan'
                    ];

                    return $this->output->set_output(json_encode($result));
                } else {
                    if ($getContact['pass_contact'] != $pass_contact) {
                        $result = [
                            'code' => 400,
                            'status' => 'failed',
                            'msg' => 'Password salah, silahkan daftar terlebih dahulu atau coba lagi'
                        ];

                        return $this->output->set_output(json_encode($result));
                    } else if ($getContact['pass_contact'] == $pass_contact) {
                        $result = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'Berhasil login, terimakasih ^_^',
                            'data' => $getContact
                        ];

                        return $this->output->set_output(json_encode($result));
                    }
                }
            }
        }
    }
}
