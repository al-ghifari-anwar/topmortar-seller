<?php

class PromoTopseller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MPromoTopseller');
    }

    public function index()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $promoTopsellers = $this->MPromoTopseller->get();

            if ($promoTopsellers == null) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Empty',
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => $promoTopsellers
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

    public function detail($id_promo_topseller)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $promoTopsellers = $this->MPromoTopseller->getById($id_promo_topseller);

            if ($promoTopsellers == null) {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Empty',
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success',
                    'data' => $promoTopsellers
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
