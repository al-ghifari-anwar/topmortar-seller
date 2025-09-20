<?php

class MPromoTopseller extends CI_Model
{
    public function get()
    {
        $query = $this->db->get_where('tb_promo_topseller')->result_array();

        return $query;
    }

    public function getById($id_promo_topseller)
    {
        $query = $this->db->get_where('tb_promo_topseller', ['id_promo_topseller' => $id_promo_topseller])->row_array();

        return $query;
    }
}
