(function (window, vj, undefined) {
    function calcStrength(password) {
        var strength = 0;
        var result = null;

        //若含有数字则增加权值
        result = password.match(/[0-9]/g);
        if (result != null) {
            strength++;
            if (result.length > 3) strength++;
        }

        //若含有大写字母或更多大写字母则增加权值
        result = password.match(/[A-Z]/g);
        if (result != null) {
            strength++;
            if (result.length > 2) strength++;
            if (result.length > 3) strength++;
        }

        //若含有特殊符号或更多特殊符号则增加权值
        result = password.match(/[^a-zA-Z0-9]/g);
        if (result != null) {
            strength++;
            if (result.length > 1) strength++;
            if (result.length > 2) strength++;
            if (result.length > 3) strength++;
        }

        //如果是纯数字，则降低权值
        if ((/^[0-9]+$/).test(password)) strength -= 3;

        //如果密码长度足够长，则增加权值
        if (password.length > 6) strength++;
        if (password.length > 10) strength++;
        if (password.length > 14) strength++;

        return strength;
    }

    function eh_hint_textbox_focus() {
        var text = $(this).data('desc');

        $(this).siblings('.desc')
            .stop(true, true)
            .html(text)
            .fadeIn('fast');
    }

    function eh_hint_textbox_blur() {
        $(this).siblings('.desc')
            .stop(true, true)
            .fadeOut('fast');
    }

    function eh_validate_textbox_blur() {
        var $textbox = $(this);

        if (
            ($textbox.data('optional') == undefined || $textbox.val().length != 0) &&	//如果不存在optional标志；或者内容不为空
                !new RegExp($textbox.data('reg')).test($textbox.val())						//则检查是否符合正则，如果不符合则进行提示
            ) {
            $textbox.siblings('.desc')
                .stop(true, true)
                .html('输入内容无效')
                .fadeIn('fast');
        }
        else {
            $textbox.siblings('.desc')
                .stop(true, true)
                .fadeOut('fast');
        }
    }

    function inline_submit(options) {
        var $form = $('#page_reg form');

        options = vj.fillParams(options,
            {
                onSuccess: vj.EmptyFunction
            });

        var $submitArea = $form.find('.ls');
        var $btn = $submitArea.find('.button'), $status = $submitArea.find('.desc');
        $btn.attr('disabled', '');
        $status.html('正在提交，请耐心等待...');

        var validation = vj.validate($form);
        if (validation !== true) {
            $btn.removeAttr('disabled');
            $status.html(validation.text);

            $(validation.element).select().focus();
        }

        var action = $form.find('input[name="action"]').val();

        vj.ajax(
            {
                action: action,
                data: options.data,
                onSuccess: function () {
                    if (action == "reg_validation") {
                        MM_showHideLayers('Sended', '', 'show');
                    }
                    if (action == "register") {
                        MM_showHideLayers('Registered', '', 'show');
                    }
                    options.onSuccess();
                },
                onFailure: function (obj) {
                    $status.html(obj.errorMsg);
                    $btn.removeAttr('disabled');
                },
                onError: function () {
                    $status.html('网络传输错误，请重试');
                    $btn.removeAttr('disabled');
                }
            });
    }

    function eh_register() {
        var $form = $('#page_reg form');
        var username = $form.find('[name="username"]').val();

        if ($form.find('[name="password"]').val() !== $form.find('[name="passwordRepeat"]').val()) {
            $form.find('.ls .desc').html('两次密码不一致，请重新输入');
            $form.find('[name="password"]').select().focus();
            return false;
        }

        inline_submit(
            {
                data: {
                    username: username,
                    password: $form.find('[name="password"]').val(),
                    sex: $form.find('[name="sex"]').val()
                },
                onSuccess: function () {
                    $('#page_reg .username_content').text(username);
                }
            });

        return false;
    }

    //发送EMail
    function eh_validation() {
        var mail = $('#page_reg form [name="email"]').val();

        inline_submit(
            {
                data: {email: mail},
                onSuccess: function () {
                    $('#page_reg .email_content').text(mail);
                    setTimeout(function () {
                        window.location.replace('/');
                    }, 1000);
                }
            });

        return false;
    }

    function init_auto_hint() {
        $('#page_reg .l').each(function () {
            //如果有描述，则在获得焦点时显示描述
            var obj = $('input.textbox', this);

            if (obj.data('desc') != undefined) {
                $('.desc', this).hide();

                obj.focus(eh_hint_textbox_focus);
                obj.blur(eh_hint_textbox_blur);
            }

            //如果有正则，则在失去焦点的时候判断是否正确
            if (obj.data('reg') != undefined)
                obj.blur(eh_validate_textbox_blur);
        });

        //密码强度
        $('#page_reg [name="password"]').bind('input', function () {
            var $textbox = $(this);

            var val = $textbox.val();

            if (val.length == 0)
                $textbox.css('background', '#FFF');
            else
                $textbox.css('background', '#' + vj.Color.blendLinear(calcStrength($(this).val()), 7, 0xFFA1A1, 0xFFC7A1, 0xFFFFA1, 0xE6EEAB, 0xC7ECB4).toString(16));
        });

        //重复密码检测
        $('#page_reg [name="passwordRepeat"]').blur(function () {
            var $textbox = $(this);

            if ($textbox.val() !== $('#page_reg [name="password"]').val())
                $textbox.siblings('.desc').stop(true, true).html('两次密码不一致').fadeIn('fast');
        });
    }

    $(document).ready(function () {
        init_auto_hint();

        switch ($('#page_reg').attr('class')) {
            case 'step1':
                $('form').bind('submit', eh_validation);
                break;
            case 'step2':
                $('form').bind('submit', eh_register);
                break;
        }

        $('#page_reg .textbox:eq(0)').focus();
    });

})(window, window.vj);
