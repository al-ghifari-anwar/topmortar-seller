<?php

class MVoucher extends CI_Model
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

    public function update($id_voucher, $voucherData)
    {
        $query = $this->db->update('tb_voucher', $voucherData, ['id_voucher' => $id_voucher]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
