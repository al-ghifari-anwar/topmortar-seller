<?php

class MDetailSuratJalan extends CI_Model
{
    public function getByIdSuratJalan($id_surat_jalan)
    {
        $this->db->join('tb_produk', 'tb_produk.id_produk = tb_detail_surat_jalan.id_produk');
        $this->db->join('tb_satuan', 'tb_satuan.id_satuan = tb_produk.id_satuan');
        $query = $this->db->get_where('tb_detail_surat_jalan', ['id_surat_jalan' => $id_surat_jalan])->result_array();

        return $query;
    }

    public function getNotFreeByIdSurat_jalan($id_surat_jalan)
    {
        $this->db->join('tb_produk', 'tb_produk.id_produk = tb_detail_surat_jalan.id_produk');
        $this->db->join('tb_satuan', 'tb_satuan.id_satuan = tb_produk.id_satuan');
        $query = $this->db->get_where('tb_detail_surat_jalan', ['id_surat_jalan' => $id_surat_jalan, 'is_bonus' => 0])->result_array();

        return $query;
    }

    public function getByIdSuratJalanLimit($id_surat_jalan)
    {
        $this->db->join('tb_produk', 'tb_produk.id_produk = tb_detail_surat_jalan.id_produk');
        $this->db->join('tb_satuan', 'tb_satuan.id_satuan = tb_produk.id_satuan');
        $query = $this->db->get_where('tb_detail_surat_jalan', ['id_surat_jalan' => $id_surat_jalan], 2)->result_array();

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
