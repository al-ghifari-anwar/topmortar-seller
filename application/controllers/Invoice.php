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
        $this->load->model('MPayment');
        $this->load->model('MPoint');
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

                    $detailLimitSuratJalans = $this->MDetailSuratJalan->getByIdSuratJalanLimit($invoice['id_surat_jalan']);

                    $invoice['total_qty'] = count($detailSuratJalans) . "";
                    $invoice['item'] = $detailLimitSuratJalans;

                    array_push($invoiceArrayData, $invoice);
                }

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => $invoiceArrayData
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
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            return $this->output->set_output(json_encode($result));
        }
    }

    public function detail()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $id_invoice = $_GET['id_invoice'];

            $invoice = $this->MInvoice->getById($id_invoice);

            if ($invoice) {
                $id_contact = $invoice['id_contact'];

                $contact = $this->MContact->getById($id_contact);

                $dateMaxCod = date('Y-m-d', strtotime("+3 days", strtotime($invoice['date_invoice'])));

                $dateJatem = date('Y-m-d', strtotime("+" . $contact['termin_payment'] . " days", strtotime($invoice['date_invoice'])));

                $invoiceArrayData = array();

                $detailSuratJalans = $this->MDetailSuratJalan->getByIdSuratJalan($invoice['id_surat_jalan']);

                $detailNotFrees = $this->MDetailSuratJalan->getNotFreeByIdSurat_jalan($invoice['id_surat_jalan']);

                $qty_not_free = 0;
                $potongan_item_cod = 0;
                $potongan_item_tempo = 0;
                foreach ($detailNotFrees as $detailNotFree) {
                    $qty_not_free += $detailNotFree['qty_produk'];
                    $potongan_item_cod += $detailNotFree['potongan_cod'] * $detailNotFree['qty_produk'];
                    $potongan_item_tempo += $detailNotFree['potongan_tempo'] * $detailNotFree['qty_produk'];
                }

                $invoice['date_jatem'] = $dateJatem;
                $invoice['date_max_cod'] = $dateMaxCod;

                $invoice['item'] = $detailSuratJalans;

                $payments = $this->MPayment->getPaymentByIdInvoice($invoice['id_invoice']);
                $totalPaid = $this->MPayment->getTotalPaymentByIdInvoice($invoice['id_invoice']);

                $discountData = null;
                $discountAmount = 0;

                // Tetap Potongan Cod (Cicil di hari sama)
                if (date('Y-m-d') <= $dateMaxCod) {
                    $discountData = [
                        'discount_name' => 'Potongan COD',
                        'discount_value' => $potongan_item_cod . "",
                        'discount_max_date' => $dateMaxCod,
                    ];
                    $discountAmount = $potongan_item_cod;
                }

                if (date('Y-m-d') > $dateMaxCod && date('Y-m-d') <= $dateJatem) {
                    $discountData = [
                        'discount_name' => 'Potongan Tepat Waktu',
                        'discount_value' => $potongan_item_tempo . "",
                        'discount_max_date' => $dateJatem,
                    ];
                    $discountAmount = $potongan_item_tempo;
                }
                // if ($payments == null) {
                // }

                $invoice['total_invoice'] = $invoice['total_invoice'] - $discountAmount . "";
                $invoice['discount_extra'] = $discountData;
                $invoice['totalPayment'] = $totalPaid['amount_payment'];
                $invoice['sisaInvoice'] = $invoice['total_invoice'] - $totalPaid['amount_payment'] . "";
                $invoice['payment'] = $payments;

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => $invoice
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
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            return $this->output->set_output(json_encode($result));
        }
    }

    public function pay()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_invoice = $post['id_invoice'];
            $amount_payment = $post['amount_payment'];

            $invoice = $this->MInvoice->getById($id_invoice);

            $invoiceTotalPayment = $this->MPayment->getTotalPaymentByIdInvoice($id_invoice);

            $totalPaid = $invoiceTotalPayment['amount_payment'] == null ? 0 : $invoiceTotalPayment['amount_payment'];

            $sisaInvoice = $invoice['total_invoice'] - $totalPaid;

            $totalItem = $this->MDetailSuratJalan->getTotalQtyByIdSuratJalan($invoice['id_surat_jalan']);

            $paymentData = [
                'amount_payment' => $amount_payment,
                'id_invoice' => $id_invoice,
                'date_payment' => date("Y-m-d H:i:s"),
                'remark_payment' => 'Paid by QRIS',
                'id_invoice' => $id_invoice,
                'source' => '8880762231',
            ];

            $savePayment = $this->MPayment->create($paymentData);

            if (!$savePayment) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Gagal payment',
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                if ($amount_payment < $invoice['total_invoice']) {
                    if ($amount_payment < $sisaInvoice) {
                        $result = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'Pembayaran berhasil',
                        ];

                        return $this->output->set_output(json_encode($result));
                    } else {
                        $invoiceData = [
                            'status_invoice' => 'paid'
                        ];

                        $saveInvoice = $this->MInvoice->update($id_invoice, $invoiceData);

                        if ($totalItem['qty_produk'] >= 100) {
                            $this->setQtyPoint($invoice['id_contact'], $id_invoice);
                        }

                        $result = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'Pembayaran berhasil',
                        ];

                        return $this->output->set_output(json_encode($result));
                    }
                } else {
                    $invoiceData = [
                        'status_invoice' => 'paid'
                    ];

                    $saveInvoice = $this->MInvoice->update($id_invoice, $invoiceData);

                    $this->setPaymentPoint($invoice['id_contact'], $id_invoice);

                    if ($totalItem['qty_produk'] >= 100) {
                        $this->setQtyPoint($invoice['id_contact'], $id_invoice);
                    }

                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Pembayaran berhasil',
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

    public function setPaymentPoint($id_contact, $id_invoice)
    {
        $pointData = [
            'id_contact' => $id_contact,
            'id_invoice' => $id_invoice,
            'source_point' => 'Full Payment',
            'val_point' => 3,
        ];

        $save = $this->MPoint->create($pointData);
    }

    public function setQtyPoint($id_contact, $id_invoice)
    {
        $pointData = [
            'id_contact' => $id_contact,
            'id_invoice' => $id_invoice,
            'source_point' => 'Full Payment',
            'val_point' => 3,
        ];

        $save = $this->MPoint->create($pointData);
    }
}
