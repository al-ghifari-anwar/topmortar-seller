<?php

class Invoice extends CI_Controller
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
        $this->load->model('MInvoice');
    }

    public function index()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_contact = $_GET['id_contact'];
            $status_filter = $_GET['status'];

            $invoices = null;

            if ($status_filter == 'ALL') {
                $invoices = $this->MInvoice->getByIdContact($id_contact);
            } else if ($status_filter == 'WAITING') {
                $invoices = $this->MInvoice->getWaitingByIdContact($id_contact);
            } else if ($status_filter == 'PAID') {
                $invoices = $this->MInvoice->getPaidByIdContact($id_contact);
            }

            if ($invoices) {
                $invoiceArrayData = array();

                foreach ($invoices as $invoice) {
                    $id_apporder = $invoice['id_apporder'];

                    $detailSuratJalans = $this->MDetailSuratJalan->getByIdSuratJalan($invoice['id_surat_jalan']);

                    $invoice['item'] = $detailSuratJalans;

                    array_push($invoiceArrayData, $invoice);
                }

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => $invoiceArrayData
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
            $id_invoice = $_GET['id_invoice'];

            $invoice = $this->MInvoice->getById($id_invoice);

            if ($invoice) {
                $invoiceArrayData = array();

                $detailSuratJalans = $this->MDetailSuratJalan->getByIdSuratJalan($invoice['id_surat_jalan']);

                $invoice['item'] = $detailSuratJalans;

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => $invoice
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
