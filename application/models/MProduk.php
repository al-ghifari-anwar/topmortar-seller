<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MProduk extends CI_Model
{
    public function get()
    {
        $result = $this->db->get('tb_produk')->result_array();

        return $result;
    }

    public function getById($id_produk)
    {
        $result = $this->db->get_where('tb_produk', ['id_produk' => $id_produk])->row_array();

        return $result;
    }

    public function getByIdCity($id_city)
    {
        $result = $this->db->get_where('tb_produk', ['id_city' => $id_city])->result_array();

        return $result;
    }
}
