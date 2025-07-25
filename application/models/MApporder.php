<?php

class MApporder extends CI_Model
{
    public function getById($id_apporder)
    {
        $query = $this->db->get_where('tb_apporder', ['id_apporder' => $id_apporder])->row_array();

        return $query;
    }

    public function getByIdContact($id_contact)
    {
        $query = $this->db->get_where('tb_apporder', ['id_contact' => $id_contact])->result_array();

        return $query;
    }

    public function create($apporderData)
    {
        $query = $this->db->insert('tb_apporder', $apporderData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
