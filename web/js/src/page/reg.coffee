define ['openvj/core', 'jquery'], (VJ, $) ->

    isFormValid = ->
        email = $('.role-email').val()
        return false if email.length is 0
        return false if not /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email)
        return true

    $('.role-email').on 'input', ->
        $('.role-reg-sendmail').prop 'disabled', not isFormValid()
        $('.role-email-error').hide()

    $('.role-reg-form').on 'submit', (event) ->
        if not isFormValid()
            $('.role-email-error').show()
            $('.role-email').focus()
            event.preventDefault()