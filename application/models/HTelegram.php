<?php

class HTelegram extends CI_Model
{
    public function sendText($chatId, $message)
    {
        $curl = curl_init();

        $telegramPayload = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.telegram.org/bot8494834740:AAEZLYfkzUhrY6GroazEJOf876oToo2-qIw/sendMessage',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($telegramPayload),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}
