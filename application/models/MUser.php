<?php


class MUser extends CI_Model
{
    public function getCourierByIdCity($id_city)
    {
        $query = $this->db->get_where('tb_courier', ['id_city' => $id_city])->row_array();

        return $query;
    }
}
