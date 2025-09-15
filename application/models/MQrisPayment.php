<?php


class MQrisPayment extends CI_Model
{
    public function create($qrisPaymentData)
    {
        $query = $this->db->insert('tb_qris_payment', $qrisPaymentData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function update($id_qris_payment, $qrisPaymentData)
    {
        $query = $this->db->update('tb_qris_payment', $qrisPaymentData, ['id_qris_payment' => $id_qris_payment]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function getUnpaid()
    {
        $query = $this->db->get_where('tb_qris_payment', ['status_qris_payment' => 'unpaid'])->result_array();

        return $query;
    }

    public function getUnpaidByIdInvoice($id_invoice)
    {
        $query = $this->db->get_where('tb_qris_payment', ['id_invoice' => $id_invoice, 'status_qris_payment' => 'unpaid'])->row_array();

        return $query;
    }

    public function getById($id_qris_payment)
    {
        $query = $this->db->get_where('tb_qris_payment', ['id_qris_payment' => $id_qris_payment])->row_array();

        return $query;
    }
}
