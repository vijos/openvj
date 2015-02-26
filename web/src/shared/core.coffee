define 'vj/core', [], ->

    $.fn.serializeObject = ->
        o = {}
        a = @serializeArray()
        $.each a, ->
            if o[@name]?
                o[@name] = [o[@name]] if !o[@name].push
                o[@name].push @value || ''
            else
                o[@name] = @value || ''
        
        return o

    $.fn.disableForm = ->
        @.find(':input:enabled').prop('disabled', true).addClass('form-disable')
        
    $.fn.enableForm = ->
        @.find(':input.form-disable').prop('disabled', false).removeClass('form-disable')

    $.fn.freezeForm = ->
        document.activeElement.blur()
        @.addClass('loading')

    $.fn.unfreezeForm = ->
        @.removeClass('loading')

    $.fn.post = (url, data) ->
        data = @.serializeObject() if not data?
        @.disableForm()
        $
        .post(url, data)
        .always => @.enableForm()

    $.fn.focusAsync = ->
        setTimeout =>
            @.focus()
        , 1000

    $.fn.showLabel = (text, type = '') ->
        $element = $(@)
        return @ if not $element.hasClass('input') and not $element.hasClass('checkbox') and not $element.is('input')
        $field = $(@).closest('.field')
        return @ if $field.length is 0
        position = $field.position()
        position.top += $field.height()
        $label = $field.children('.ui.label.absolute')
        $label = $('<div>').css(top: position.top - 10, left: position.left, opacity: 0).appendTo($field) if $label.length is 0
        $label.velocity('stop').text(text).attr('class', 'ui pointing label absolute').addClass(type)
        $label.velocity
            duration: 200
            top: position.top
            opacity: 1
        , easing: 'easeOutCubic'
        @

    $.fn.hideLabel = ->
        $element = $(@)
        return @ if not $element.hasClass('input') and not $element.hasClass('checkbox') and not $element.is('input')
        $field = $(@).closest('.field')
        return @ if $field.length is 0
        $label = $field.children('.ui.label.absolute:not(.hiding)')
        return @ if $label.length is 0
        position = $field.position()
        position.top += $field.height()
        $label.velocity('stop').addClass('hiding').velocity
            top: position.top - 10
            opacity: 0
        ,
            duration: 200
            easing: 'easeInCubic'
            complete: -> $label.remove()
        @
    
    # append CSRF token on every ajax request
    if CSRFToken?
        $.ajaxPrefilter (options) ->
            options.headers = {} if not options.headers?
            options.headers['x-csrf-token'] = CSRFToken

    return {}