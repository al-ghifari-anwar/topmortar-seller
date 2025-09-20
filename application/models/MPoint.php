<?php

class MPoint extends CI_Model
{
    public function getTotalPointByIdContact($id_contact)
    {
        $this->db->select('COALESCE(SUM(val_point), 0) AS val_point');
        $query = $this->db->get_where('tb_point', ['id_contact' => $id_contact])->row_array();

        return $query;
    }

    public function getPointByIdContact($id_contact)
    {
        $query = $this->db->get_where('tb_point', ['id_contact' => $id_contact])->result_array();

        return $query;
    }

    public function create($pointData)
    {
        $query = $this->db->insert('tb_point', $pointData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
