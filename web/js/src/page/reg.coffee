define ['openvj/core', 'jquery', 'openvj/twig'], (VJ, $, twig) ->
    
    isFormValid = ->
        email = $('.role-email').val()
        return false if email.length is 0
        return false if not /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email)
        return true

    $('.role-email').on 'input', ->
        $('.role-reg-sendmail').prop 'disabled', not isFormValid()
        $('.role-email-error').hide()

    $('.role-reg-form').on 'submit', (event) ->
        event.preventDefault()
        if not isFormValid()
            $('.role-email-error').show()
            $('.role-email').focus()
            return
        email = $('.role-email').val()
        $(@).disableForm()
        $
        .post '/reg', email: email
        .done -> $('.reg-form').html twig.renderTag('template-email-sent', {email: email})
        .fail (xhr) -> alert xhr.responseJSON.message
        .always => $(@).enableForm()