<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MTukang extends CI_Model
{
    public $nama;
    public $nomorhp;
    public $tgl_lahir;
    public $id_city;
    public $maps_url;
    public $address;
    public $id_catcus;
    public $id_contact;

    public function get()
    {
        $result = $this->db->get('tb_tukang')->result_array();

        return $result;
    }

    public function getById($id_tukang)
    {
        $result = $this->db->get_where('tb_tukang', ['id_tukang' => $id_tukang])->row_array();

        return $result;
    }

    public function create()
    {
        $post = $this->input->post();
        $this->nama = $post['nama'];
        $this->nomorhp = $post['nomorhp'];
        $this->tgl_lahir = $post['tgl_lahir'];
        $this->id_city = $post['id_city'];
        $this->maps_url = $post['maps_url'];
        $this->address = $post['address'];
        $this->id_catcus = $post['id_catcus'];
        $this->id_contact = $post['id_contact'];

        $insert = $this->db->insert('tb_tukang', $this);

        if ($insert) {
            return true;
        } else {
            return false;
        }
    }
}
