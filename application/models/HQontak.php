<?php

class HQontak extends CI_Model
{
    public function sendNotifText($id_distributor, $to_number, $to_name, $template_id, $message, $from_name)
    {
        // 
        $qontak = $this->db->get_where('tb_qontak', ['id_distributor' => $id_distributor])->row_array();
        $integration_id = $qontak['integration_id'];
        $wa_token = $qontak['token'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                        "to_number": "' . $to_number . '",
                        "to_name": "' . $to_name . '",
                        "message_template_id": "' . $template_id . '",
                        "channel_integration_id": "' . $integration_id . '",
                        "language": {
                            "code": "id"
                        },
                        "parameters": {
                            "body": [
                            {
                                "key": "1",
                                "value": "to_name",
                                "value_text": "' . $to_name . '"
                            },
                            {
                                "key": "2",
                                "value": "message",
                                "value_text": "' . trim(preg_replace('/\s+/', ' ', $message)) . '"
                            },
                            {
                                "key": "3",
                                "value": "from_name",
                                "value_text": "' . $from_name . '"
                            }
                            ]
                        }
                        }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $wa_token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $res = json_decode($response, true);

        return $res;
    }
}
