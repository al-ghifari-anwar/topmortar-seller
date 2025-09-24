<?php

class MPayment extends CI_Model
{
    public function getLastPaymentByIdInvoice($id_invoice)
    {
        $this->db->order_by('tb_payment.date_payment', 'DESC');
        $query = $this->db->get_where('tb_payment', ['id_invoice' => $id_invoice])->row_array();

        return $query;
    }

    public function getPaymentByIdContact($id_contact)
    {
        $this->db->join('tb_invoice', 'tb_invoice.id_invoice = tb_payment.id_invoice');
        $this->db->join('tb_surat_jalan', 'tb_surat_jalan.id_surat_jalan = tb_invoice.id_surat_jalan');
        $this->db->order_by('tb_payment.date_payment', 'DESC');
        $query = $this->db->get_where('tb_payment', ['tb_surat_jalan.id_contact' => $id_contact])->result_array();

        return $query;
    }

    public function getPaymentByIdInvoice($id_invoice)
    {
        // $this->db->join('tb_invoice', 'tb_invoice.id_invoice = tb_payment.id_invoice');
        // $this->db->join('tb_surat_jalan', 'tb_surat_jalan.id_surat_jalan = tb_invoice.id_surat_jalan');
        $this->db->order_by('tb_payment.date_payment', 'DESC');
        $query = $this->db->get_where('tb_payment', ['id_invoice' => $id_invoice])->result_array();

        return $query;
    }

    public function getTotalPaymentByIdInvoice($id_invoice)
    {
        $this->db->select('SUM(amount_payment) AS amount_payment');
        $query = $this->db->get_where('tb_payment', ['id_invoice' => $id_invoice])->row_array();

        return $query;
    }

    public function create($paymentData)
    {
        $query = $this->db->insert('tb_payment', $paymentData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
