<?php

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Qris extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MQrisPayment');
        $this->load->model('MInvoice');
        $this->load->model('MPayment');
        $this->load->model('MPoint');
        $this->load->model('MDetailSuratJalan');
        $this->load->model('MContact');
    }

    public function checkPayment()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_invoice = $post['id_invoice'];

            $qrisPayment = $this->MQrisPayment->getUnpaidByIdInvoice($id_invoice);

            if ($qrisPayment) {
                $qrisPayment['img_qris_payment'] = base_url("/assets/img/qris_img/") . $qrisPayment['img_qris_payment'];

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Payment ongoing',
                    'data' => $qrisPayment,
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'No payment found, proceed to create',
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

    public function requestPayment()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_invoice = $post['id_invoice'];
            $amount_payment = $post['amount_payment'];

            $invoice = $this->MInvoice->getById($id_invoice);

            $getUnpaidQris = $this->MQrisPayment->getUnpaidByIdInvoice($id_invoice);

            if ($getUnpaidQris) {
                // $max_date_qris_payment = date('Y-m-d H:i:s', strtotime("+30 minutes", strtotime($getUnpaidQris['date_qris_payment'])));
                $getUnpaidQris['img_qris_payment'] = base_url("/assets/img/qris_img/") . $getUnpaidQris['img_qris_payment'];

                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Already have ongoing payment',
                    'data' => $getUnpaidQris,
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $apiKey = '139139250813480';
                $mID = '126301287';
                // Request QRIS API
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://qris.interactive.co.id/restapi/qris/show_qris.php?do=create-invoice&apikey=' . $apiKey . '&mID=' . $mID . '&cliTrxNumber=' . $id_invoice . '&cliTrxAmount=' . $amount_payment . '&useTip=no',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: TiPMix=30.8422431151935; x-ms-routing-name=self'
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                $resQris = json_decode($response, true);

                if ($resQris['status'] != 'success') {
                    $result = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Gagal, harap coba lagi',
                    ];

                    return $this->output->set_output(json_encode($result));
                } else {
                    $qrisData = $resQris['data'];

                    // Qris Response Data
                    $qrisContent = $qrisData['qris_content'];
                    $qrisRequestDate = $qrisData['qris_request_date'];
                    $qrisInv = $qrisData['qris_invoiceid'];
                    $qrisNmid = $qrisData['qris_nmid'];

                    // QR Image Generation
                    $qrisFileName = 'qris_' . $id_invoice . '_' . time() . '.png';
                    $qrisFilePath = FCPATH . 'assets/img/qris_img/' . $qrisFileName;
                    // Generate QR
                    $qrisQr = QrCode::create($qrisContent)->setSize(300)->setMargin(10);

                    $writer = new PngWriter();
                    $writerResult = $writer->write($qrisQr);
                    $writerResult->saveToFile($qrisFilePath);

                    $qrisPaymentData = [
                        'id_apporder' => $invoice['id_apporder'],
                        'id_invoice' => $id_invoice,
                        'amount_qris_payment' => $amount_payment,
                        'img_qris_payment' => $qrisFileName,
                        'content_qris_payment' => $qrisContent,
                        'date_qris_payment' => $qrisRequestDate,
                        'inv_qris_payment' => $qrisInv,
                        'nmid_qris_payment' => $qrisNmid,
                        'status_qris_payment' => 'unpaid',
                    ];

                    $save = $this->MQrisPayment->create($qrisPaymentData);

                    if (!$save) {
                        $result = [
                            'code' => 400,
                            'status' => 'failed',
                            'msg' => 'Not found',
                        ];

                        return $this->output->set_output(json_encode($result));
                    } else {
                        $id_qris_payment = $this->db->insert_id();

                        $qrisPayment = $this->MQrisPayment->getById($id_qris_payment);

                        $qrisPayment['img_qris_payment'] = base_url("/assets/img/qris_img/") . $qrisPayment['img_qris_payment'];

                        $result = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'QRIS berhasil dibuat',
                            'data' => $qrisPayment,
                        ];

                        return $this->output->set_output(json_encode($result));
                    }
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

    public function getQrisPayment()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_qris_payment = $post['id_qris_payment'];

            $qrisPayment = $this->MQrisPayment->getById($id_qris_payment);

            if (!$qrisPayment) {
                $result = [
                    'code' => 400,
                    'status' => 'ok',
                    'msg' => 'Tidak ada pembayaran',
                    'data' => [],
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $qrisPayment['img_qris_payment'] = base_url("/assets/img/qris_img/") . $qrisPayment['img_qris_payment'];

                if ($qrisPayment['status_qris_payment'] == 'paid') {
                    $result = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Pembayaran sukses',
                        'data' => $qrisPayment,
                    ];

                    return $this->output->set_output(json_encode($result));
                } else {
                    $id_qris_payment = $qrisPayment['id_qris_payment'];

                    $max_date_qris_payment = date('Y-m-d H:i:s', strtotime("+30 minutes", strtotime($qrisPayment['date_qris_payment'])));

                    if (date("Y-m-d H:i:s") >= $max_date_qris_payment) {
                        $qrisPaymentData = [
                            'status_qris_payment' => 'expired',
                        ];

                        $this->MQrisPayment->update($id_qris_payment, $qrisPaymentData);

                        $result = [
                            'code' => 400,
                            'status' => 'failed',
                            'msg' => 'QRIS Expired, silahkan ulangi pembayaran',
                        ];

                        return $this->output->set_output(json_encode($result));
                    } else {
                        $result = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'QRIS sudah siap',
                            'data' => $qrisPayment,
                        ];

                        return $this->output->set_output(json_encode($result));
                    }
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

    public function checkStatus()
    {
        $this->output->set_content_type('application/json');

        $qrisPayments = $this->MQrisPayment->getUnpaid();

        foreach ($qrisPayments as $qrisPayment) {
            $inv_qris_payment = $qrisPayment['inv_qris_payment'];
            $date_qris_payment = date("Y-m-d", strtotime($qrisPayment['date_qris_payment']));
            $amount_qris_payment = $qrisPayment['amount_qris_payment'];

            $apiKey = '139139250813480';
            $mID = '126301287';
            // Check Status QRIS
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://qris.interactive.co.id/restapi/qris/checkpaid_qris.php?do=checkStatus&apikey=' . $apiKey . '&mID=' . $mID . '&invid=' . $inv_qris_payment . '&trxvalue=' . $amount_qris_payment . '&trxdate=' . $date_qris_payment,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: TiPMix=89.41549226921767; x-ms-routing-name=self'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $resStatus = json_decode($response, true);

            if ($resStatus['status'] == 'success') {
                $statusData = $resStatus['data'];

                if ($statusData['qris_status'] == 'paid') {
                    $qris_status = $statusData['qris_status'];
                    $qris_customer = $statusData['qris_payment_customername'];
                    $qris_method = $statusData['qris_payment_methodby'];
                    $qris_paid_date = $statusData['qris_paid_date'];
                    $qris_version = $resStatus['qris_api_version_code'];

                    $qrisPaymentData = [
                        'status_qris_payment' => 'paid',
                        'paid_at' => $qris_paid_date,
                        'customer_qris_payment' => $qris_customer,
                        'method_qris_payment' => $qris_method,
                        'version_qris_payment' => $qris_version,
                    ];

                    $save = $this->MQrisPayment->update($qrisPayment['id_qris_payment'], $qrisPaymentData);

                    if ($save) {
                        $id_invoice = $qrisPayment['id_invoice'];

                        $remark_payment = 'QRIS#' . $inv_qris_payment . ' - ' . $qris_customer . '/' . $qris_method;

                        $this->insertPayment($qrisPayment['id_qris_payment'], $id_invoice, $amount_qris_payment, $remark_payment, $qris_paid_date);
                    }
                }
            }
        }

        $result = [
            'code' => 200,
            'status' => 'ok',
            'msg' => 'Check Status Done',
        ];

        return $this->output->set_output(json_encode($result));
    }

    public function insertPayment($id_qris_payment, $id_invoice, $amount_payment, $remark_payment, $date_payment)
    {
        $invoice = $this->MInvoice->getById($id_invoice);

        $contact = $this->MContact->getById($invoice['id_contact']);

        $dateMaxCod = date('Y-m-d', strtotime("+3 days", strtotime($invoice['date_invoice'])));

        $dateJatem = date('Y-m-d', strtotime("+" . $contact['termin_payment'] . " days", strtotime($invoice['date_invoice'])));

        $invoiceTotalPayment = $this->MPayment->getTotalPaymentByIdInvoice($id_invoice);

        $totalPaid = $invoiceTotalPayment['amount_payment'] == null ? 0 : $invoiceTotalPayment['amount_payment'];

        $detailNotFrees = $this->MDetailSuratJalan->getNotFreeByIdSurat_jalan($invoice['id_surat_jalan']);

        $qty_not_free = 0;
        $potongan_item_cod = 0;
        $potongan_item_tempo = 0;
        foreach ($detailNotFrees as $detailNotFree) {
            $qty_not_free += $detailNotFree['qty_produk'];
            $potongan_item_cod += $detailNotFree['potongan_cod'] * $detailNotFree['qty_produk'];
            $potongan_item_tempo += $detailNotFree['potongan_tempo'] * $detailNotFree['qty_produk'];
        }

        $discountData = [];
        $discountAmount = 0;
        $discountName = null;

        if (date('Y-m-d') <= $dateMaxCod) {
            $discountData = [
                'discount_name' => 'Potongan COD',
                'discount_value' => $potongan_item_cod . "",
            ];
            $discountAmount = $potongan_item_cod;
            $discountName = 'Potongan COD';
        }

        if (date('Y-m-d') > $dateMaxCod && date('Y-m-d') <= $dateJatem) {
            $discountData = [
                'discount_name' => 'Potongan Tepat Waktu',
                'discount_value' => $potongan_item_tempo . "",
            ];

            $discountAmount = $potongan_item_tempo;
            $discountName = 'Potongan Tepat Waktu';
        }

        $total_invoice = $invoice['total_invoice'] - $discountAmount;

        $sisaInvoice = $invoice['total_invoice'] - $totalPaid - $discountAmount;

        $totalItem = $this->MDetailSuratJalan->getTotalQtyByIdSuratJalan($invoice['id_surat_jalan']);

        $paymentData = [
            'id_qris_payment' => $id_qris_payment,
            'amount_payment' => $amount_payment,
            'id_invoice' => $id_invoice,
            'date_payment' => $date_payment,
            'remark_payment' => $remark_payment,
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
            if ($amount_payment < $total_invoice) {
                if ($amount_payment < $sisaInvoice) {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Pembayaran berhasil',
                    ];

                    return $this->output->set_output(json_encode($result));
                } else {
                    $paidInvoice = $this->MInvoice->getPaidByIdContact($contact['id_contact']);
                    $countPaidInvoice = count($paidInvoice);

                    $lastPayment = $this->MPayment->getLastPaymentByIdInvoice($id_invoice);

                    $date_payment = date('Y-m-d', strtotime($lastPayment['date_payment']));

                    if ($date_payment == date("Y-m-d")) {
                        $this->setPaymentPoint($invoice['id_contact'], $id_invoice);

                        if ($totalItem['qty_produk'] >= 100) {
                            $this->setQtyPoint($invoice['id_contact'], $id_invoice);
                        }

                        $this->setFrequencyPoint($contact['id_contact'], $id_invoice, $countPaidInvoice);
                    }

                    $invoiceData = [
                        'discount_extra_name' => $discountName,
                        'discount_extra_amount' => $discountAmount,
                        'total_invoice' => $total_invoice,
                        'status_invoice' => 'paid'
                    ];

                    $saveInvoice = $this->MInvoice->update($id_invoice, $invoiceData);

                    // if ($totalItem['qty_produk'] >= 100) {
                    //     $this->setQtyPoint($invoice['id_contact'], $id_invoice);
                    // }

                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Pembayaran berhasil',
                    ];

                    return $this->output->set_output(json_encode($result));
                }
            } else {
                $invoiceData = [
                    'discount_extra_name' => $discountName,
                    'discount_extra_amount' => $discountAmount,
                    'total_invoice' => $total_invoice,
                    'status_invoice' => 'paid'
                ];

                $paidInvoice = $this->MInvoice->getPaidByIdContact($contact['id_contact']);
                $countPaidInvoice = count($paidInvoice);

                $saveInvoice = $this->MInvoice->update($id_invoice, $invoiceData);

                $this->setPaymentPoint($invoice['id_contact'], $id_invoice);

                if ($totalItem['qty_produk'] >= 100) {
                    $this->setQtyPoint($invoice['id_contact'], $id_invoice);
                }

                $this->setFrequencyPoint($contact['id_contact'], $id_invoice, $countPaidInvoice);

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Pembayaran berhasil',
                ];

                return $this->output->set_output(json_encode($result));
            }
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
            'source_point' => 'Qty over 100 Point',
            'val_point' => 1,
        ];

        $save = $this->MPoint->create($pointData);
    }

    public function setFrequencyPoint($id_contact, $id_invoice, $countPaidInvoice)
    {
        $val_point = 2;

        if ($countPaidInvoice > 1) {
            $val_point = 1;
        }

        $pointData = [
            'id_contact' => $id_contact,
            'id_invoice' => $id_invoice,
            'source_point' => 'Frequency order point',
            'val_point' => $val_point,
        ];

        $save = $this->MPoint->create($pointData);
    }
}
