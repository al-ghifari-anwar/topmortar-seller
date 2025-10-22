<?php

class MVoucher extends CI_Controller
{
    public function getNotClaimedByIdContact($id_contact)
    {
        $this->db->order_by('tb_voucher.date_voucher', 'DESC');
        $query = $this->db->get_where('tb_voucher', ['tb_voucher.id_contact' => $id_contact, 'is_claimed' => 0, 'DATE(exp_date) >' => date('Y-m-d')])->result_array();

        return $query;
    }

    public function getClaimedByIdContact($id_contact)
    {
        $this->db->order_by('tb_voucher.date_voucher', 'DESC');
        $query = $this->db->get_where('tb_voucher', ['tb_voucher.id_contact' => $id_contact, 'is_claimed' => 1])->result_array();

        return $query;
    }

    public function getAllByIdContact($id_contact)
    {
        $this->db->order_by('tb_voucher.date_voucher', 'DESC');
        $query = $this->db->get_where('tb_voucher', ['tb_voucher.id_contact' => $id_contact])->result_array();

        return $query;
    }
}
