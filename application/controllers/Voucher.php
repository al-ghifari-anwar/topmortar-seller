<?php


class Voucher extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MVoucher');
    }

    public function index()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_contact = $_GET['id_contact'];
            $is_claimed = $_GET['is_claimed'];

            $vouchers = null;

            if ($is_claimed == 1) {
                $vouchers = $this->MVoucher->getClaimedByIdContact($id_contact);
            } else {
                $vouchers = $this->MVoucher->getNotClaimedByIdContact($id_contact);
            }

            if ($vouchers) {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Sucecss',
                    'data' => $vouchers
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Voucher tidak ditemukan'
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
}
