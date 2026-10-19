<?php

class MPointRedeem extends CI_Model
{
    public function create($pointRedeemData)
    {
        $query = $this->db->insert('tb_point_redeem', $pointRedeemData);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
