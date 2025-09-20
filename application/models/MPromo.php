<?php

class MPromo extends CI_Model
{
    public function getById($id_promo)
    {
        $query = $this->db->get_where('tb_promo', ['id_promo' => $id_promo])->row_array();

        return $query;
    }
}
