<?php


class MUser extends CI_Model
{
    public function getCourierByIdCity($id_city)
    {
        $query = $this->db->get_where('tb_user', ['id_city' => $id_city, 'level_user' => 'courier'])->row_array();

        return $query;
    }
}
