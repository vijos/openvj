define ['vj/core'], (VJ) ->
    $('.role-login-form').on 'submit', (event) ->
        form = $(this).serializeObject()
        if form.username.length is 0
            $('.role-username').focus()
            event.preventDefault()
            return