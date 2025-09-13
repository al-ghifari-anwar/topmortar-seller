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
                $getUnpaidQris['img_qris_payment'] = FCPATH . "/assets/img/qris_img/" . $getUnpaidQris['img_qris_payment'];

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

    public function getQrisPayment($id_qris_payment)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $qrisPayment = $this->MQrisPayment->getById($id_qris_payment);

            if (!$qrisPayment) {
                $result = [
                    'code' => 400,
                    'status' => 'ok',
                    'msg' => 'Tidak ada pembayaran',
                    'data' => $qrisPayment,
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                if ($qrisPayment['status_qris_payment'] == 'paid') {
                    $result = [
                        'code' => 200,
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
}
