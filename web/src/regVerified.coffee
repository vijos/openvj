define ['vj/core', 'vj/twig'], (VJ, twig) ->

    $('.ui.checkbox').checkbox()

    isFormValid = ->
        form = $('.role-reg-form').serializeObject()
        return 'username' if form.username.length < 3 or form.username.length > 16
        return 'username' if not form.username.match /^\S*$/
        return 'accept' if form.accept isnt 'accept'
        return true

    $('.role-username, .role-accept').on 'input focus change', ->
        $('.role-reg-complete').prop 'disabled', isFormValid() isnt true

    $('.role-username')
    .on 'input', ->
        $(@).hideLabel()
    .on 'blur', ->
        $(@).closest('.field').toggleClass('error', isFormValid() is 'username')
    .on 'focus', ->
        $(@).closest('.field').removeClass('error')
    .focus()

    $('.role-reg-form').on 'submit', (event) ->
        event.preventDefault()
        if isFormValid() isnt true
            $('.role-username').focus()
            return
        form = $(@).serializeObject()
        $(@).disableForm()
        $
        .post '/reg/complete', form
        .always => $(@).enableForm()
        .fail (xhr) ->
            if xhr.responseJSON.code is 'UserManager.createUser.user_exists'
                $('.role-username').showLabel(xhr.responseJSON.message, 'red')
                setTimeout ->
                    $('.role-username').focus()
                , 0
                return