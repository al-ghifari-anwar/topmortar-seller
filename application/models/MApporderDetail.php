<?php

class MApporderDetail extends CI_Model
{
    public function getByIdApporder($id_apporder)
    {
        $this->db->join('tb_produk', 'tb_produk.id_produk = tb_apporder_detail.id_produk');
        $this->db->join('tb_satuan', 'tb_satuan.id_satuan = tb_produk.id_satuan');
        $query = $this->db->get_where('tb_apporder_detail', ['id_apporder' => $id_apporder])->result_array();

        return $query;
    }

    public function getByIdApporderLimit($id_apporder)
    {
        $this->db->join('tb_produk', 'tb_produk.id_produk = tb_apporder_detail.id_produk');
        $this->db->join('tb_satuan', 'tb_satuan.id_satuan = tb_produk.id_satuan');
        $query = $this->db->get_where('tb_apporder_detail', ['id_apporder' => $id_apporder], 2)->result_array();

        return $query;
    }

    public function create($apporderDetailData)
    {
        $query = $this->db->insert('tb_apporder_detail', $apporderDetailData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
