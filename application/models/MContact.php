<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MContact extends CI_Model
{
    public function getById($id_contact)
    {
        $this->db->join('tb_city', 'tb_city.id_city = tb_contact.id_city');
        $result = $this->db->get_where('tb_contact', ['id_contact' => $id_contact])->row_array();

        return $result;
    }

    public function resetPassword()
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $id_contact = $post['id_contact'];
        $pass_contact = md5("TopSeller" . md5($post['pass_contact']));

        $result = $this->db->update('tb_contact', ['pass_contact' => $pass_contact, 'quota_priority' => 5], ['id_contact' => $id_contact]);

        return $result;
    }

    public function getByNomorhp()
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
        $nomorhp = $post['nomorhp'];
        $nomorhp = "62" . substr($nomorhp, 1);

        // $this->db->where('tb_contact.is_deleted', 0);
        $this->db->join('tb_city', 'tb_city.id_city = tb_contact.id_city');
        $result = $this->db->get_where('tb_contact', ['nomorhp' => $nomorhp])->row_array();

        return $result;
    }

    public function delete($id_contact)
    {
        $save = $this->db->update('tb_contact', ['is_deleted' => 1], ['id_contact' => $id_contact]);

        if ($save) {
            return true;
        } else {
            return false;
        }
    }
}
