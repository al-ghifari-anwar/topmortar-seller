<?php

class Point extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPoint');
        $this->load->model('MPointRedeem');
        $this->load->model('MMerchandise');
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

            return $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            return $this->output->set_output(json_encode($result));
        }
    }

    public function redeem()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_contact = $post['id_contact'];
            $id_merchandise = $post['id_merchandise'];
            $price_merchandise = $post['price_merchandise'];
            $source_point = 'Penukaran Merchandise';

            $merchandise = $this->MMerchandise->getById($id_merchandise);

            if (!$merchandise) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Merchandise tidak ditemukan',
                ];

                return $this->output->set_output(json_encode($result));
            }

            $points = $this->MPoint->getTotalPointByIdContact($id_contact);

            if ($points['val_point'] < $price_merchandise) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Point tidak mencukupi',
                ];

                return $this->output->set_output(json_encode($result));
            }

            $pointRedeemData = [
                'id_contact' => $id_contact,
                'id_merchandise' => $id_merchandise,
                'val_point_redeem' => $price_merchandise,
                'desc_point_redeem' => 'Penukaran Merchandise - ' . $merchandise['name_merchandise'],
                'status_point_redeem' => 'Pending',
            ];

            $save = $this->MPointRedeem->create($pointRedeemData);

            if (!$save) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Failed',
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $id_point_redeem = $this->db->insert_id();

                $pointData = [
                    'id_contact' => $id_contact,
                    'id_invoice' => 0,
                    'source_point' => $source_point,
                    'val_point' => (int)-$price_merchandise,
                    'id_point_redeem' => $id_point_redeem,
                ];

                $save = $this->MPoint->create($pointData);

                if (!$save) {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Not found',
                    ];

                    return $this->output->set_output(json_encode($result));
                } else {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Success redeem voucher',
                    ];

                    return $this->output->set_output(json_encode($result));
                }
            }
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            return $this->output->set_output(json_encode($result));
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

            return $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            return $this->output->set_output(json_encode($result));
        }
    }
}
