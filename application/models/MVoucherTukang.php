<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MVoucherTukang extends CI_Model
{
    public $id_tukang;
    public $id_contact;
    public $no_seri;
    public $updated_at;
    public $exp_at;

    public function getByIdMd5($id_md5)
    {
        $this->db->order_by('created_at', 'DESC');
        $result = $this->db->get_where('tb_voucher_tukang', ['id_md5' => $id_md5])->row_array();

        return $result;
    }

    public function claim($id_md5, $id_contact)
    {
        $result = $this->db->update('tb_voucher_tukang', ['id_contact' => $id_contact, 'is_claimed' => 1, 'claim_date' => date("Y-m-d H:i:s")], ['id_md5' => $id_md5]);

        return $result;
    }
}
