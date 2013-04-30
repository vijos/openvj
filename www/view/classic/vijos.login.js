function vj_login() {
    var $form = $('#login');
    alert($form);
    return;
    //var $status = $form.find('.desc');

    //$status.stop(true).slideUp(100);

    if ($form.find('[name="user"]').val().trim().length == 0) {
        $form.find('[name="user"]').focus();
        $status.stop(true, true).html('请输入用户名').slideDown(100);
        return false;
    }

    if ($form.find('[name="pass"]').val().trim().length == 0) {
        $form.find('[name="pass"]').focus();
        $status.stop(true, true).html('请输入密码').slideDown(100);
        return false;
    }

    vj.ajax(
        {
            action: 'login',
            data: {
                user: $form.find('[name="user"]').val(),
                pass: $form.find('[name="pass"]').val()
            },
            onSuccess: function () {
                window.location.reload();
            },
            onFailure: function (obj) {
                $status.stop(true, true).html(obj.errorMsg).slideDown(100);
            },
            onError: function () {
                $status.stop(true, true).html('网络错误，请重试').slideDown(100);
            }
        });

    return false;
}


