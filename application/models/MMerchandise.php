<?php

class MMerchandise extends CI_Model
{
    public function get()
    {
        $this->db->order_by('price_merchandise', 'ASC');
        $query = $this->db->get('tb_merchandise')->result_array();

        return $query;
    }

    public function getById($id_merchandise)
    {
        $query = $this->db->get_where('tb_merchandise', ['id_merchandise' => $id_merchandise])->row_array();

        return $query;
    }
}
