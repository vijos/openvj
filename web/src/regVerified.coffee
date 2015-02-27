define ['vj/core', 'vj/twig'], (VJ, twig) ->

    $('.ui.checkbox').checkbox()

    shouldFormEnabled = ->
        form = $('.role-reg-form').serializeObject()
        return false if form.username.length < 3 or form.username.length > 16
        return false if not form.username.match /^\S*$/
        return false if form.accept isnt 'accept'
        return true

    isFormValid = ->
        form = $('.role-reg-form').serializeObject()
        return 'username' if form.username.length < 3 or form.username.length > 16
        return 'username' if not form.username.match /^\S*$/
        return 'password' if form.password.length > 50
        return 'password_mismatch' if form['password-repeat'] isnt form.password
        return 'accept' if form.accept isnt 'accept'
        return true

    $('.role-username, .role-accept').on 'input focus change', ->
        $('.role-reg-complete').prop 'disabled', shouldFormEnabled() isnt true

    $('.role-username, .role-password, .role-password-repeat').on 'input blur', ->
        $(@).hideLabel()

    $('.role-username')
    .on 'blur', ->
        $(@).closest('.field').toggleClass('error', isFormValid() is 'username')
    .on 'focus', ->
        $(@).closest('.field').removeClass('error')
    .focus()

    $('.role-reg-form').on 'submit', (event) ->
        event.preventDefault()
        validation = isFormValid()
        if validation is 'username'
            $('.role-username').focus()
            return
        if validation is 'password'
            $('.role-password').showLabel('密码长度不能超过 50 位', 'red').focus()
            return
        if validation is 'password_mismatch'
            $('.role-password-repeat').showLabel('两次密码不一致', 'red').focus()
            return
        if validation isnt true
            # other cases
            return
        form = $(@).serializeObject()
        $(@)
        .post '/reg/complete'
        .done ->
            $('.role-target').html twig.renderTag('template-complete',
                email: form.email
                username: form.username
            )
            $('.reg-verified-form').addClass('completed')
            
            setTimeout ->
                window.location = '/'
            , 4000
            
        .fail (xhr) -> $('.role-username').showLabel(xhr.responseJSON.message, 'red').focus()