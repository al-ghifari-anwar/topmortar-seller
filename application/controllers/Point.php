<?php

class Point extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPoint');
        $this->load->model('MPointRedeem');
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_contact = $_GET['id_contact'];

            $points = $this->MPoint->getPointByIdContact($id_contact);

            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $points
            ];

            $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            $this->output->set_output(json_encode($result));
        }
    }

    public function redeem()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_contact = $post['id_contact'];
            $id_merchandise = $post['id_merchandise'];
            $val_point = $post['price_merchandise'];
            $source_point = 'Penukaran Merchandise';

            $pointRedeemData = [
                'id_contact' => $id_contact,
                'id_merchandise' => $id_merchandise,
                'val_point_redeem' => $val_point,
                'desc_point_redeem' => 'Penukaran Merchandise',
                'status_point_redeem' => 'Pending',
            ];

            $save = $this->MPointRedeem->create($pointRedeemData);

            if (!$save) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Failed',
                ];

                $this->output->set_output(json_encode($result));
            } else {
                $id_point_redeem = $this->db->insert_id();

                $pointData = [
                    'id_contact' => $id_contact,
                    'id_invoice' => 0,
                    'source_point' => $source_point,
                    'val_point' => (int)-$val_point,
                    'id_point_redeem' => $id_point_redeem,
                ];

                $save = $this->MPoint->create($pointData);

                if (!$save) {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Not found',
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Success redeem voucher',
                    ];

                    $this->output->set_output(json_encode($result));
                }
            }
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            $this->output->set_output(json_encode($result));
        }
    }

    public function getTotal()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_contact = $_GET['id_contact'];

            $point = $this->MPoint->getTotalPointByIdContact($id_contact);

            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $point
            ];

            $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
