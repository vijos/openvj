define 'vj/twig', ['twig'], (twig) ->
    
    renderTag = (id, params) ->
        templateStr = document.getElementById(id).innerHTML
        twigTemplate = twig.twig data:templateStr
        twigTemplate.render params

    TwigHelper =
        renderTag: renderTag

    return TwigHelper