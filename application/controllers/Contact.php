<?php

class Contact extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MContact');
    }

    public function delete()
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $id_contact = $post['id_contact'];

        $delete = $this->MContact->delete($id_contact);

        if ($delete) {
            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Sukses menghapus data',
            ];

            $this->output->set_output(json_encode($result));
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Gagal menghapus data',
                'detail' => $this->db->error()
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
