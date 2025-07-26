<?php


class MInvoice extends CI_Model
{
    public function getByIdContact($id_contact)
    {
        $this->db->join('tb_surat_jalan', 'tb_surat_jalan.id_surat_jalan = tb_invoice.id_surat_jalan');
        $this->db->join('tb_contact', 'tb_contact.id_contact = tb_surat_jalan.id_contact');
        $this->db->join('tb_city', 'tb_city.id_city = tb_contact.id_city');
        $this->db->where('tb_surat_jalan.id_contact', $id_contact);
        $this->db->order_by('id_invoice', 'DESC');
        $query = $this->db->get('tb_invoice')->result_array();

        return $query;
    }
}
