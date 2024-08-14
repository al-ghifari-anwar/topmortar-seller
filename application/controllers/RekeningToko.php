<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RekeningToko extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('MContact');
        $this->load->model('MOtpToko');
        $this->load->model('MRekeningToko');
        $this->load->model('MBank');
    }

    public function index()
    {
        redirect("https://topmortarindonesia.com");
    }

    public function create()
    {
        $this->output->set_content_type('application/json');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $id_contact = $post['id_contact'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $create = $this->MRekeningToko->create();

            if (!$create) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Tidak ada data'
                ];

                $this->output->set_output(json_encode($result));
            } else {
                $getRekening = $this->MRekeningToko->getByIdContact($id_contact);

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Sukses menyimpan nomor rekening',
                    'data' => $getRekening
                ];

                $this->output->set_output(json_encode($result));
            }
        } else {
            $result = [
                'code' => 404,
                'status' => 'failed',
                'msg' => 'Not Found'
            ];

            $this->output->set_output(json_encode($result));
        }
    }

    public function getByIdContact($id_contact)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $getRekening = $this->MRekeningToko->getByIdContact($id_contact);

            if ($getRekening == null) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Tidak ada data'
                ];

                $this->output->set_output(json_encode($result));
            } else {

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Sukses mengambil nomor rekening',
                    'data' => $getRekening
                ];

                $this->output->set_output(json_encode($result));
            }
        } else {
            $result = [
                'code' => 404,
                'status' => 'failed',
                'msg' => 'Not Found'
            ];

            $this->output->set_output(json_encode($result));
        }
    }

    public function update($id_rekening_toko)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {

            $getRekening = $this->MRekeningToko->update($id_rekening_toko);

            if (!$getRekening) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Gagal menupdate rekening'
                ];

                $this->output->set_output(json_encode($result));
            } else {

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Sukses mengedit nomor rekening'
                ];

                $this->output->set_output(json_encode($result));
            }
        } else {
            $result = [
                'code' => 404,
                'status' => 'failed',
                'msg' => 'Not Found'
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
