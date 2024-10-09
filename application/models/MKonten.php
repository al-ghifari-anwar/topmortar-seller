<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MKonten extends CI_Model
{
    public function get()
    {
        $result = $this->db->get('tb_konten')->result_array();

        return $result;
    }

    public function getById($id_konten)
    {
        $result = $this->db->get_where('tb_konten', ['id_konten' => $id_konten])->row_array();

        return $result;
    }
}
