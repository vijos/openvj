define ['openvj/core', 'jquery', 'openvj/twig'], (VJ, $, twig) ->
    
    isFormValid = ->
        form = $('.role-reg-form').serializeObject()
        return false if form.username.length < 3 or form.username.length > 16
        return false if not form.username.match /^\S*$/
        return true

    $('.role-username').on 'input', ->
        $('.role-reg-complete').prop 'disabled', not isFormValid()
    