$(".js_btn_test").click(function (e) {
    e.preventDefault();

    diafan_ajax.init({
        data: {
            action: 'test',
            module: 'cloudkassir'
        },
        success: function (response) {
            if (response.data)
            {
                $("#test_check").text(prepare(response.data));
            }
        }
    });
});