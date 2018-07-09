<?php

/**
* Shop_admin_order
*/
class Shop_admin_order extends Frame_admin {

    after public function prepare_config() {
        $this->variables["main"]["receipt_cancel"] = array(
            'type' => 'function',
        );
        $this->diafan->_admin->js_view[] = "modules/cloudkassir/admin/js/receipt_cancel.admin.inc.js";
    }

    new public function edit_variable_receipt_cancel() {
        echo '<div class="unit">'
            . '<p><button class="btn js_btn_receipt_cancel" type="button">' . $this->diafan->_('Произвести возврат прихода') . '</button></p>'
            . '<p id="receipt_result"></p>'
            . '</div>';
    }

}