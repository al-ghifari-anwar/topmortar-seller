<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MRekeningToko extends CI_Model
{
    public $id_contact;
    public $to_name;
    public $id_bank;
    public $to_account;
    public $updated_at;

    public function get()
    {
        $result = $this->db->get('tb_rekening_toko')->result_array();

        return $result;
    }

    public function getById($id_rekening_toko)
    {
        $result = $this->db->get_where('tb_rekening_toko', ['id_rekening_toko' => $id_rekening_toko])->row_array();

        return $result;
    }

    public function getByIdContact($id_contact)
    {
        $this->db->join('tb_bank', 'tb_bank.id_bank = tb_rekening_toko.id_bank');
        $result = $this->db->get_where('tb_rekening_toko', ['id_contact' => $id_contact])->row_array();

        return $result;
    }

    public function create()
    {
        date_default_timezone_set('Asia/Jakarta');
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $this->id_contact = $post['id_contact'];
        $this->to_name = $post['to_name'];
        $this->id_bank = $post['id_bank'];
        $this->to_account = $post['to_account'];
        $this->updated_at = date("Y-m-d H:i:s");

        $result = $this->db->insert('tb_rekening_toko', $this);

        return $result;
    }
}
