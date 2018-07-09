<?php

/**
 * Обработка POST-запросов в административной части модуля
 */
if (!defined('DIAFAN')) {
    $path = __FILE__;
    $i = 0;
    while (!file_exists($path . '/includes/404.php')) {
        if ($i == 10)
            exit;
        $i++;
        $path = dirname($path);
    }
    include $path . '/includes/404.php';
}

class Cloudkassir_admin_action extends Action_admin {

    public function init() {
        if (empty($_POST["action"]))
            return false;

        switch ($_POST["action"]) {
            case 'test':
                $this->result['data'] = $this->diafan->_cloudkassir->test();

                break;
            case 'receipt_cancel':
                try {
                    $id = $this->diafan->_cloudkassir->sell_refund($_POST['order_id']);
                    $this->result['data'] = array(
                        'success' => true,
                        'message' => $this->diafan->_("Запрос возврата прихода успешно выполнен", false),
                        'id' => $id
                    );
                }
                catch (Exception $e) {
                    $msg = $e->getMessage();
                    if (empty($msg)) {
                        $msg = 'Произошла неизвестная ошибка';
                    }
                    $this->result['data'] = array(
                        'success' => false,
                        'message' => $msg,
                    );
                }

                break;
        }
    }

}
