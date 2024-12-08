define(['jquery', 'bootstrap'], function ($) {
    var Controller = {
        index: function () {
            //初始化表单验证
            $('#login-form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $submit = $form.find('button[type="submit"]');
                $submit.attr('disabled', true);
                
                $.ajax({
                    url: '/index/index/login',
                    type: 'post',
                    dataType: 'json',
                    data: $form.serialize(),
                    success: function (response) {
                        if (response.code === 1) {
                            location.href = response.url;
                        } else {
                            Layer.msg(response.msg);
                            $submit.attr('disabled', false);
                        }
                    },
                    error: function () {
                        Layer.msg('系统错误，请稍后重试');
                        $submit.attr('disabled', false);
                    }
                });
                
                return false;
            });
        }
    };
    return Controller;
});