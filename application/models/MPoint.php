<?php

class MPoint extends CI_Model
{
    public function getTotalPointByIdContact($id_contact)
    {
        $this->db->select('SUM(val_point) AS val_point');
        $query = $this->db->get_where('tb_point', ['id_contact' => $id_contact])->row_array();

        return $query;
    }

    public function getPointByIdContact($id_contact)
    {
        $query = $this->db->get_where('tb_point', ['id_contact' => $id_contact]);

        return $query;
    }
}
