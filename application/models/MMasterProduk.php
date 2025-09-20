<?php

class MMasterProduk extends CI_Model
{
    public function getById($id_master_produk)
    {
        $query = $this->db->get_where('tb_master_produk', ['id_master_produk' => $id_master_produk])->row_array();

        return $query;
    }
}
