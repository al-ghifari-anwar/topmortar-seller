<?php

class Livechat extends CI_Controller
{
    public function getToken()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $id_contact = $post['id_contact'];
            $nama = $post['nama'];
            $nomorhp = $post['nomorhp'];

            $contact = $this->db->join('tb_city', 'tb_city.id_city = tb_contact.id_city')->where('tb_contact.id_contact', $id_contact)->get('tb_contact')->row_array();

            $haloai = $this->db->where('id_distributor', $contact['id_distributor'])->get('tb_haloai')->row_array();

            $payload = [
                'externalUserId' => $id_contact,
                'displayName' => $nama,
                'phone' => $nomorhp,
                'ttlSeconds' => 2592000,
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.haloai.co.id/api/open/inApp/v1/clientToken',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $haloai['server_key_haloai'],
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $resArray = json_decode($response);

            if ($resArray['status'] == "ok") {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success redeem voucher',
                    'data' => $resArray['data'],
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Error',
                    'detail' => $resArray,
                ];

                return $this->output->set_output(json_encode($result));
            }
        } else {
            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Not found',
            ];

            return $this->output->set_output(json_encode($result));
        }
    }
}
