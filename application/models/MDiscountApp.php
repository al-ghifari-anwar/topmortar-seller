<?php

class MDiscountApp extends CI_Model
{
    public function get()
    {
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('tb_discount_app', 1)->row_array();

        return $query;
    }
}
