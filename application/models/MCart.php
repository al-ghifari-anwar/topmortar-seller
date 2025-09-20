<?php

class MCart extends CI_Model
{
    public function getByIdContact($id_contact)
    {
        $query = $this->db->get_where('tb_cart', ['id_contact' => $id_contact, 'status_cart' => 'active'])->row_array();

        return $query;
    }

    public function create($cartData)
    {
        $query = $this->db->insert('tb_cart', $cartData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function update($cartData, $id_cart)
    {
        $query = $this->db->update('tb_cart', $cartData, ['id_cart' => $id_cart]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
