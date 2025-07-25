<?php

class MDetailSuratJalan extends CI_Model
{
    public function getByIdSuratJalan($id_surat_jalan)
    {
        $query = $this->db->get_where('tb_detail_surat_jalan', ['id_surat_jalan' => $id_surat_jalan])->result_array();

        return $query;
    }

    public function create($detailSuratJalanData)
    {
        $query = $this->db->insert('tb_detail_surat_jalan', $detailSuratJalanData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
