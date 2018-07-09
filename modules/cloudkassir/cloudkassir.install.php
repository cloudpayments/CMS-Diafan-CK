<?php

/**
 * Установка модуля
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

class Cloudkassir_install extends Install {

    /**
     * @var string название
     */
    public $title = "CloudKassir";

    /**
     * @var array записи в таблице {modules}
     */
    public $modules = array(
        array(
            "name" => "cloudkassir",
            "admin" => true,
            "site" => true,
            "site_page" => false,
        ),
    );

    /**
     * @var array меню административной части
     */
    public $admin = array(
        array(
            "name" => "CloudKassir",
            "rewrite" => "cloudkassir",
            "group_id" => "2",
            "act" => true,
            "children" => array(
                array(
                    "name" => "CloudKassir",
                    "rewrite" => "cloudkassir",
                    "act" => true,
                ),
            )
        ),
    );
}
