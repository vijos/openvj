define ['vj/core', 'vj/twig'], (VJ, twig) ->
    
    isFormValid = ->
        email = $('.role-email').val()
        return false if email.length is 0
        return false if not /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email)
        return true

    $('.role-email')
    .on 'input', ->
        $(@).hideLabel()
    .on 'input focus', ->
        $('.role-reg-sendmail').prop 'disabled', not isFormValid()
    .focus()

    $('.role-reg-form').on 'submit', (event) ->
        event.preventDefault()
        if not isFormValid()
            $('.role-email').focus()
            return

        email = $('.role-email').val()
        $(@)
        .post '/reg'
        .done -> $('.role-target').html twig.renderTag('template-email-sent', email: email)
        .fail (xhr) -> $('.role-email').showLabel(xhr.responseJSON.message, 'red').focus()
    