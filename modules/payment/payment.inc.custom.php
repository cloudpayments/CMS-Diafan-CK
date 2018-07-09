<?php

class Payment_inc extends Diafan {

    replace public function success($pay, $type = 'all')
    {
        if($type == 'all' || $type == 'pay')
        {
            if($pay['module_name'] == 'cart')
            {
                $this->diafan->_shop->order_pay($pay['element_id']);
            }
            elseif($pay["module_name"] == 'balance')
            {
                $this->diafan->_balance->pay($pay);
            }
            DB::query("UPDATE {payment_history} SET status='pay', created=%d WHERE id=%d", time(), $pay["id"]);
        }

        switch ($type) {
            case 'all':
            case 'pay':
                if ('cart' == $pay['module_name']) {
                    $this->diafan->_cloudkassir->sell($pay['element_id']);
                }
                break;
        }

        if($type == 'all' || $type == 'redirect')
        {
            $order_rew = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='site' AND trash='0' AND element_type='element' AND element_id IN (SELECT id FROM {site} WHERE module_name='%s' AND [act]='1' AND trash='0')", $pay['module_name']);
            $this->diafan->redirect('http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").'/'.(REVATIVE_PATH ? REVATIVE_PATH.'/' : '').$order_rew.'/step3/');
        }
    }
}
