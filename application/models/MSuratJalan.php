<?php

class MSuratJalan extends CI_Model
{
    public function getByNoSuratJalan($no_surat_jalan)
    {
        $query = $this->db->get_where('tb_surat_jalan', ['no_surat_jalan' => $no_surat_jalan])->row_array();

        return $query;
    }

    public function getById($id_surat_jalan)
    {
        $this->db->join('tb_contact', 'tb_contact.id_contact = tb_surat_jalan.id_contact');
        $this->db->join('tb_user', 'tb_user.id_user = tb_surat_jalan.id_courier');
        $this->db->join('tb_kendaraan', 'tb_kendaraan.id_kendaraan = tb_surat_jalan.id_kendaraan');
        $this->db->join('tb_city', 'tb_city.id_city = tb_contact.id_city');
        $query = $this->db->get_where('tb_surat_jalan', ['id_surat_jalan' => $id_surat_jalan])->row_array();

        return $query;
    }

    public function create($suratJalanData)
    {
        $query = $this->db->insert('tb_surat_jalan', $suratJalanData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
