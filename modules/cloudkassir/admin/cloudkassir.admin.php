<?php

/**
 * Настройки модуля
 * 
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

class Cloudkassir_admin extends Frame_admin {

    /**
     * @var array поля в базе данных для редактирования
     */
    public $variables = array(
        'config' => array(
            'hr1' => array(
                'type' => 'title',
                'name' => 'Настройки безопасности',
            ),
            'public_id' => array(
                'type' => 'text',
                'name' => 'Идентификатор сайта',
                'help' => 'Обязательный идентификатор сайта. Находится в ЛК CloudPayments.'
            ),
            'secret_key' => array(
                'type' => 'password',
                'name' => 'Секретный ключ',
                'help' => 'Обязательный секретный ключ. Находится в ЛК CloudPayments (Пароль для API).'
            ),
            'hr2' => array(
                'type' => 'title',
                'name' => 'Информация об организации',
            ),
            'inn' => array(
                'type' => 'text',
                'name' => 'ИНН',
                'help' => 'ИНН вашей организации или ИП, на который зарегистрирована касса.',
            ),
            'taxation_system' => array(
                'type' => 'select',
                'name' => 'Система налогообложения',
                'help' => 'Система налогообложения магазина.',
                'select' => array(
                    'ts_0' => 'Общая система налогообложения',
                    'ts_1' => 'Упрощенная система налогообложения (Доход)',
                    'ts_2' => 'Упрощенная система налогообложения (Доход минус Расход)',
                    'ts_3' => 'Единый налог на вмененный доход',
                    'ts_4' => 'Единый сельскохозяйственный налог',
                    'ts_5' => 'Патентная система налогообложения',
                )
            ),
            'vat' => array(
                'type' => 'select',
                'name' => 'Ставка НДС',
                'help' => 'Ставка НДС.',
                'select' => array(
                    'vat_none' => 'НДС не облагается',
                    'vat_0'    => 'НДС 0%',
                    'vat_10'   => 'НДС 10%',
                    'vat_18'   => 'НДС 18%',
                    'vat_110'  => 'Расчетный НДС 10/110',
                    'vat_118'  => 'Расчетный НДС 18/118',
                ),
            ),
            'vat_delivery' => array(
                'type' => 'select',
                'name' => 'Ставка НДС для доставки',
                'help' => 'Ставка НДС для доставки.',
                'select' => array(
                    'vat_none' => 'НДС не облагается',
                    'vat_0'    => 'НДС 0%',
                    'vat_10'   => 'НДС 10%',
                    'vat_18'   => 'НДС 18%',
                    'vat_110'  => 'Расчетный НДС 10/110',
                    'vat_118'  => 'Расчетный НДС 18/118',
                ),
            ),
            'hr3' => 'hr',
            'test' => array(
                'type' => 'function',
            ),
        )
    );

    /**
     * @var array настройки модуля
     */
    public $config = array(
        'config', // файл настроек модуля
    );

    public function edit_config_variable_test() {
        echo '<div class="unit">'
        . '<p><button class="btn js_btn_test" type="button">' . $this->diafan->_('Создать проверочный чек') . '</button></p>'
        . '<p><pre id="test_check"></pre></p>'
        . '</div>';
    }

}
