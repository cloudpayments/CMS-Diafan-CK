$(".js_btn_receipt_cancel").click(function (e) {
    e.preventDefault();

    diafan_ajax.init({
        data: {
            action: 'receipt_cancel',
            module: 'cloudkassir',
            order_id: $('input[name=id]').val(),
        },
        success: function (response) {
            if (response.data)
            {
                if (response.data.success) {
                    $("#receipt_result").html(prepare(response.data['message']));
                } else {
                    alert(response.data['message']);
                }
            }
            if(response.errors)
            {
                $.each(response.errors, function (k, val) {
                    alert(val);
                });
            }
        }
    });
});