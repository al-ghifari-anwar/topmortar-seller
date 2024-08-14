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

    public function claim()
    {
        $this->output->set_content_type('application/json');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $id_contact = $post['id_contact'];
        $id_md5 = $post['id_md5'];

        $getVoucher = $this->MVoucherTukang->getByIdMd5($id_md5);

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
                    $to_name = $getRekeningToko['to_name'];
                    $to_account = $getRekeningToko['to_account'];
                    $is_bca = $getRekeningToko['is_bca'];
                    $nama_bank = $getRekeningToko['nama_bank'];
                    $swift_code = $getRekeningToko['swift_bank'];

                    if ($is_bca == 1) {
                        // TF intrabank
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://apibca.topmortarindonesia.com/snapIntrabankVctukang.php?to=' . $to_account,
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
                            $this->MVoucherTukang->claim($id_md5, $id_contact);

                            $result = [
                                'code' => 200,
                                'status' => 'failed',
                                'msg' => 'Claim voucher berhasil, dana telah masuk ke rekening / e-wallet anda'
                            ];

                            $this->output->set_output(json_encode($result));
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
                                'msg' => 'Proses claim gagal'
                            ];

                            $this->output->set_output(json_encode($result));
                        } else {
                            $this->MVoucherTukang->claim($id_md5, $id_contact);

                            $result = [
                                'code' => 200,
                                'status' => 'failed',
                                'msg' => 'Claim voucher berhasil, dana telah masuk ke rekening / e-wallet anda'
                            ];

                            $this->output->set_output(json_encode($result));
                        }
                    }
                }
            }
        }
    }
}
