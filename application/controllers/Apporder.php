<?php

class Apporder extends CI_Controller
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
    }

    public function index()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_contact = $_GET['id_contact'];
            // ALL, DIPROSES, DIKIRIM, SELESAI 
            $status_filter = $_GET['status'];

            $apporders = $this->MApporder->getByIdContact($id_contact);

            $appordersArray = array();

            foreach ($apporders as $apporder) {
                $id_apporder = $apporder['id_apporder'];

                $apporderDetails = $this->MApporderDetail->getByIdApporder($id_apporder);

                $apporderLimitDetails = $this->MApporderDetail->getByIdApporderLimit($id_apporder);

                $suratJalan = $this->MSuratJalan->getByIdApporder($id_apporder);

                $status_apporder = 'DIPROSES';

                if ($suratJalan) {
                    if ($suratJalan['is_closing'] == 0) {
                        $status_apporder = 'DIKIRIM';
                    } else {
                        $status_apporder = 'SELESAI';
                    }
                }

                $apporder['status_apporder'] = $status_apporder;
                $apporder['total_qty'] = count($apporderDetails) . "";
                $apporder['items'] = $apporderLimitDetails;

                array_push($appordersArray, $apporder);
            }

            if ($status_filter == 'ALL') {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => $appordersArray
                ];

                $this->output->set_output(json_encode($result));
            } else {
                $appordersArrayFilter = array_filter($appordersArray, function ($item) use ($status_filter) {
                    return $item['status_apporder'] === $status_filter;
                });

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => array_values($appordersArrayFilter)
                ];

                $this->output->set_output(json_encode($result));
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

    public function detail()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_apporder = $_GET['id_apporder'];

            $apporder = $this->MApporder->getById($id_apporder);

            // $appordersArray = array();

            // foreach ($apporders as $apporder) {
            //     $id_apporder = $apporder['id_apporder'];

            $apporderDetails = $this->MApporderDetail->getByIdApporder($id_apporder);

            $suratJalan = $this->MSuratJalan->getByIdApporder($id_apporder);

            $status_apporder = 'DIPROSES';

            if ($suratJalan) {
                if ($suratJalan['is_closing'] == 0) {
                    $status_apporder = 'DIKIRIM';
                } else {
                    $status_apporder = 'SELESAI';
                }
            }

            $apporder['status_apporder'] = $status_apporder;
            $apporder['total_qty'] = count($apporderDetails) . "";
            $apporder['items'] = $apporderDetails;

            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $apporder
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
