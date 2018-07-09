<?php

if (!defined('DIAFAN')) {
    $path = __FILE__;
    $i    = 0;
    while (!file_exists($path . '/includes/404.php')) {
        if ($i == 10) {
            exit;
        }
        $i++;
        $path = dirname($path);
    }
    include $path . '/includes/404.php';
}

/**
 * @property string inn
 * @property string taxation_system
 * @property string vat
 * @property string vat_delivery
 * @property string public_id
 * @property string secret_key
 */
class Cloudkassir_inc extends Model {

    const MODULE_NAME = 'cloudkassir';

    public function test() {
        $return = array('result' => null, 'exception' => null);

        try {
            $c        = DB::query_result("SELECT COUNT(*) FROM {shop_order}");
            $order_id = DB::query_result("SELECT id FROM {shop_order} LIMIT 1 OFFSET " . rand(0, $c));

            $return['result'] = $this->sell($order_id);

        } catch (CloudkassirException $ex) {
            $return['exception'] = $ex->getMessage();
        }

        return var_export($return, true);
    }

    /**
     * Получение переменных из конфигурации модуля
     * @param string $name
     * @return string|bool
     */
    public function __get($name) {
        return $this->diafan->configmodules($name, self::MODULE_NAME);
    }

    /**
     * Создание переменных в конфигурации модуля
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    public function __set($name, $value) {
        return $this->diafan->configmodules($name, self::MODULE_NAME, false, false, $value);
    }

    public function __construct(&$diafan) {
        parent::__construct($diafan);

        Custom::inc('plugins/httprequest/httprequest.php');
    }

    /**
     * Проверка ответа на наличие ошибок
     * @param array $response
     * @return boolean
     * @throws CloudkassirException
     */
    private function error($response) {
        if (isset($response["Success"]) && $response["Success"]) {
            return false;
        }
        Dev::$exception_field = 'message';

        throw new CloudkassirException($response["Message"]);
    }

    /**
     * Создает POST запрос к API
     * @param string $method
     * @param array  $data
     * @return \DHttpRequest
     */
    private function request($method, $data) {

        $http = DHttpRequest::post("https://api.cloudpayments.ru/" . $method)
                            ->form(json_encode($data))
                            ->header("Authorization", "Basic " . base64_encode($this->public_id . ":" . $this->secret_key))
                            ->contentType(DHttpRequest::CONTENT_TYPE_JSON);

        return $http;
    }

    /**
     * Получает e-mail пользователя, оформившего заказ
     * копия функции из shop/inc/shop.inc.order.php потому что она private
     *
     * @param  integer $order_id
     * @return string
     */
    private function get_email($order_id) {
        $mail = DB::query_result("SELECT e.value FROM {shop_order_param_element} AS e INNER JOIN 
			{shop_order_param} AS p ON e.param_id=p.id AND p.trash='0' AND e.trash='0' 
			WHERE p.type='email' AND e.element_id=%d", $order_id);

        if (!$mail && $user_id = DB::query_result("SELECT user_id FROM {shop_order} WHERE id=%d AND trash='0' LIMIT 1", $order_id)) {
            $mail = DB::query_result("SELECT mail FROM {users} WHERE id=%d  AND trash='0' LIMIT 1", $user_id);
        }

        return $mail;
    }

    /**
     * Получает телефон пользователя, оформившего заказ
     *
     * @param  integer $order_id
     * @return string
     */
    private function get_phone($order_id) {
        $phone = DB::query_result("SELECT ph.value FROM {shop_order_param_element} AS ph INNER JOIN 
			{shop_order_param} AS p ON ph.param_id=p.id AND p.trash='0' AND ph.trash='0' 
			WHERE p.type='phone' AND ph.element_id=%d", $order_id);

        if ($phone) {
            $phone = preg_replace('/\D/', '', $phone);
            if (strlen($phone) == 11 && $phone[0] == '8') {
                $phone[0] = '7';
            }
        }

        return $phone;
    }

    /**
     * Конвертирует в float результат работы функции number_format
     * @param string $number
     * @return float
     */
    private function parse_number($number) {
        $dec_point = $this->diafan->configmodules("format_price_2", "shop");

        return floatval(str_replace($dec_point, '.', preg_replace('/[^\d' . preg_quote($dec_point) . ']/', '', $number)));
    }

    /**
     * чек «Приход»
     * @param int $order_id нормер заказа
     * @return string  Уникальный идентификатор чека
     * @throws CloudkassirException
     */
    public function sell($order_id) {
        return $this->createReceipt($order_id, 'Income');
    }

    /**
     * чек «Возврат прихода»
     * @param int $order_id нормер заказа
     * @return string  Уникальный идентификатор чека
     * @throws CloudkassirException
     */
    public function sell_refund($order_id) {
        return $this->createReceipt($order_id, 'IncomeReturn');
    }

    /**
     * чек «Расход»
     * @param int $order_id нормер заказа
     * @return string  Уникальный идентификатор чека
     * @throws CloudkassirException
     */
    public function buy($order_id) {
        return $this->createReceipt($order_id, 'Expense');
    }

    /**
     * чек «Возврат расхода»
     * @param int $order_id нормер заказа
     * @return string  Уникальный идентификатор чека
     * @throws CloudkassirException
     */
    public function buy_refund($order_id) {
        return $this->createReceipt($order_id, 'ExpenseReturn');
    }

    /**
     * POST запрос для чеков расхода, прихода, возврат расхода и возврат прихода.
     * @param int $order_id
     * @param enum string $operation
     * @return string Уникальный идентификатор чека
     * @throws CloudkassirException
     */
    private function createReceipt($order_id, $operation) {

        $client_email = $this->get_email($order_id);
        $client_phone = $this->get_phone($order_id);
        $info         = $this->diafan->_shop->order_get($order_id);

        $request = array(
            'Inn'             => $this->inn,
            'Type'            => $operation,
            'CustomerReceipt' => array(
                'Items'          => array(),
                'taxationSystem' => str_replace('ts_', '', $this->taxation_system),
                'email'          => $client_email,
                'phone'          => $client_phone
            ),
            'InvoiceId'       => $order_id,
            'AccountId'       => $client_email,
        );

        //Распраделяем скидку по товарам
        if (!empty($info["discount_summ"])) {
            $s = 0;
            foreach ($info['rows'] as $r) {
                $s += $r["summ"];
            }
            $orderSumm = $this->parse_number($info["summ"]);
            if (!empty($info["delivery"])) {
                $orderSumm -= $this->parse_number($info["delivery"]["summ"]);
            }
            if (!empty($info["additional_cost"])) {
                foreach ($info["additional_cost"] as $row) {
                    $orderSumm -= $this->parse_number($row["summ"]);
                }
            }
            foreach ($info["rows"] as &$r) {
                $r["price"] = $this->parse_number($r["price"]) * ($orderSumm / $s);
                $r["summ"]  = $r["price"] * floatval($r["count"]);
            }
        } else {
            foreach ($info["rows"] as &$r) {
                $r["price"] = $this->parse_number($r["price"]);
                $r["summ"]  = $r["price"] * floatval($r["count"]);
            }
        }

        //Какая-то проблема, если в селекте в настройках встречается 0, поэтому все настройки с префиксом
        $vat = str_replace("vat_", "", $this->vat);
        if ($vat == "none") {
            $vat = "";
        }
        $items = array();
        foreach ($info['rows'] as $row) {
            $items[] = array(
                "label"    => $row['name'] . ($row["article"] ? " " . $row["article"] : ""),
                "price"    => $row['price'],
                "quantity" => intval($row['count']),
                "amount"   => $row['summ'],
                "vat"      => $vat
            );

        }

        if (!empty($info["additional_cost"])) {
            foreach ($info["additional_cost"] as $row) {
                $items[] = array(
                    "label"    => $row['name'],
                    "quantity" => 1,
                    "price"    => $this->parse_number($row["summ"]),
                    "amount"   => $this->parse_number($row["summ"]),
                    "vat"      => $vat,
                );
            }
        }
        if (!empty($info["delivery"])) {
            $delivery_cost = $this->parse_number($info["delivery"]["summ"]);
            if ($delivery_cost > 0) {
                $vat_delivery = str_replace("vat_", "", $this->vat_delivery);
                if ($vat_delivery == "none") {
                    $vat_delivery = "";
                }
                $items[] = array(
                    "label"    => $this->diafan->_("Доставка", false),
                    "quantity" => 1,
                    "price"    => $delivery_cost,
                    "amount"   => $delivery_cost,
                    "vat"      => $vat_delivery,
                );
            }
        }
        $request["CustomerReceipt"]["Items"] = $items;

        try {
            $http = $this->request("kkt/receipt", $request);
            if (!$http->ok()) {
                throw new CloudkassirException($http->message(), $http->code());
            }
            $response = json_decode($http->body(), true);

            if (!$this->error($response)) {
                return $response['Model']['Id'];
            }
        } catch (DHttpRequestException $ex) {
            throw new CloudkassirException($ex->getMessage(), $ex->getCode());
        }
    }

}

class CloudkassirException extends Exception {

}
