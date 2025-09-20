<?php

class MSettingTopseller extends CI_Model
{
    public function get()
    {
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('tb_setting_topseller', 1)->row_array();

        return $query;
    }
}
