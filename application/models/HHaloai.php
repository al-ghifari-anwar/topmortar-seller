<?php

class HHaloai extends CI_Model
{
    public function sendNotifText($id_distributor, $to_number, $to_name, $template, $message, $from_name)
    {
        // 
        $haloai = $this->db->get_where('tb_haloai', ['id_distributor' => $id_distributor])->row_array();
        $wa_token = $haloai['token_haloai'];
        $business_id = $haloai['business_id_haloai'];
        $channel_id = $haloai['channel_id_haloai'];

        $sender = $from_name;

        $haloaiPayload = [
            'activate_ai_after_send' => false,
            'channel_id' => $channel_id,
            'fallback_template_message' => $template,
            'fallback_template_variables' => [
                $to_name,
                trim(preg_replace('/\s+/', ' ', $message)),
                $sender,
            ],
            'phone_number' => $to_name,
            'text' => trim(preg_replace('/\s+/', ' ', $message)),
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.haloai.co.id/api/open/channel/whatsapp/v1/sendMessageByPhoneSync',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($haloaiPayload),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $wa_token,
                'X-HaloAI-Business-Id: ' . $business_id,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $res = json_decode($response, true);

        return $res;
    }
}
