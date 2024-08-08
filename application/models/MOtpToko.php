<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MOtpToko extends CI_Model
{
    public $id_contact;
    public $otp;
    public $exp_at;

    public function getByIdContact($id_contact)
    {
        $this->db->order_by('created_at', 'DESC');
        $result = $this->db->get_where('tb_otp_toko', ['id_contact' => $id_contact], 1)->row_array();

        return $result;
    }

    public function getForVerify()
    {
        date_default_timezone_set('Asia/Jakarta');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $id_contact = $post['id_contact'];
        $otp = $post['otp'];

        $result = $this->db->get_where('tb_otp_toko', ['id_contact' => $id_contact, 'otp' => $otp])->row_array();

        return $result;
    }

    public function updateUsed($id_contact)
    {
        $result = $this->db->update('tb_otp_toko', ['is_used' => 1], ['id_contact' => $id_contact]);

        return $result;
    }

    public function create()
    {
        date_default_timezone_set('Asia/Jakarta');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $this->id_contact = $post['id_contact'];
        $this->otp = rand(100000, 999999);
        $this->exp_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $result = $this->db->insert('tb_otp_toko', $this);

        return $result;
    }
}
