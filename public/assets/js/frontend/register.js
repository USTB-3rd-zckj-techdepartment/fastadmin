define(['jquery', 'bootstrap'], function ($) {
    var Controller = {
        index: function () {
            //初始化表单验证
            $('#register-form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $submit = $form.find('button[type="submit"]');
                
                // 验证密码是否一致
                var password = $('#password').val();
                var repassword = $('#repassword').val();
                if (password !== repassword) {
                    Layer.msg('两次输入的密码不一致');
                    return false;
                }
                
                // 禁用提交按钮防止重复提交
                $submit.attr('disabled', true);
                
                $.ajax({
                    url: '/index/index/register',
                    type: 'post',
                    dataType: 'json',
                    data: $form.serialize(),
                    success: function (response) {
                        if (response.code === 1) {
                            Layer.msg(response.msg, {time: 1500}, function() {
                                location.href = response.url;
                            });
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