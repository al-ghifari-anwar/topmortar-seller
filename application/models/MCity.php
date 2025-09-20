<?php

class MCity extends CI_Model
{
    public function getById($id_city)
    {
        $query = $this->db->get_where('tb_city', ['id_city' => $id_city])->row_array();

        return $query;
    }
}
