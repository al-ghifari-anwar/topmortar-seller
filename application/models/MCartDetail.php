<?php

class MCartDetail extends CI_Model
{
    public function getByIdCart($id_cart)
    {
        $this->db->join('tb_produk', 'tb_produk.id_produk = tb_cart_detail.id_produk');
        $this->db->join('tb_master_produk', 'tb_produk.id_master_produk = tb_master_produk.id_master_produk');
        $this->db->join('tb_satuan', 'tb_satuan.id_satuan = tb_produk.id_satuan');
        $query = $this->db->get_where('tb_cart_detail', ['id_cart' => $id_cart])->result_array();

        return $query;
    }

    public function getByIdCartAndIdProduct($id_cart, $id_produk)
    {
        $this->db->join('tb_produk', 'tb_produk.id_produk = tb_cart_detail.id_produk');
        $this->db->join('tb_master_produk', 'tb_produk.id_master_produk = tb_master_produk.id_master_produk');
        $this->db->join('tb_satuan', 'tb_satuan.id_satuan = tb_produk.id_satuan');
        $query = $this->db->get_where('tb_cart_detail', ['id_cart' => $id_cart, 'tb_cart_detail.id_produk' => $id_produk])->row_array();

        return $query;
    }

    public function create($cartDetailData)
    {
        $query = $this->db->insert('tb_cart_detail', $cartDetailData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function update($id_cart_detail, $cartDetailData)
    {
        $query = $this->db->update('tb_cart_detail', $cartDetailData, ['id_cart_detail' => $id_cart_detail]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id_cart_detail)
    {
        $query = $this->db->delete('tb_cart_detail', ['id_cart_detail' => $id_cart_detail]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
