define ['jquery'], ($) ->

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
    
    # append CSRF token on every ajax request
    if CSRFToken?
        $.ajaxPrefilter (options) ->
            options.headers = {} if not options.headers?
            options.headers['x-csrf-token'] = CSRFToken

    return {}