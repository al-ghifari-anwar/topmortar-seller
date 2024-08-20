<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBank extends CI_Model
{
    public function get()
    {
        $result = $this->db->get('tb_bank')->result_array();

        return $result;
    }

    public function getById($id_bank)
    {
        $result = $this->db->get_where('tb_bank', ['id_bank' => $id_bank])->row_array();

        return $result;
    }
}
