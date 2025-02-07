<?php

class MStok extends CI_Model
{
    public function getStokIn($id_produk)
    {
        $this->db->select('SUM(jml_stok) AS jml_stok');
        $result = $this->db->get_where('tb_stok', ['id_produk' => $id_produk, 'status_stok' => 'in'])->row_array();

        return $result;
    }

    public function getStokOut($id_produk)
    {
        $this->db->select('SUM(jml_stok) AS jml_stok');
        $result = $this->db->get_where('tb_stok', ['id_produk' => $id_produk, 'status_stok' => 'out'])->row_array();

        return $result;
    }
}
