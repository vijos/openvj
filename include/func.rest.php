<?php

namespace rest;

/**
 * Include file when exists
 *
 * @param $path
 */
function _exec_if_exists($path)
{
    if (file_exists($path)) {
        require $path;
        exit();
    }
}

/**
 * Find a controller to handle the request. The URI is parsed and divided into
 * parts by path separator. The parts are stored in a list. If there exists
 * a remaining part in the part list, the function uses it as the action.
 * Otherwise, the value in the $default parameter is used. The function joins
 * all of the actions to form a path, and tries to find a corresponding
 * controller exactly with the path. If the path does not exists, the function
 * tries to use the alternative list provided by the $alternative parameter.
 * The alternative list is an associative array, the keys are regular
 * expressions and the values are paths. If the path matches a key, or if the
 * key is empty, the value is used as the alternative path. This function
 * never returns
 *
 * @param null $default
 * @param null $alternative
 */

function uri_route($default = null, $alternative = null)
{
    global $_URI_LIST, $_ACTION_LIST;

    // Parse the URI to form a list at first call
    if ($_ACTION_LIST === null) {
        $uri           = parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH);
        $_URI_LIST    = array_reverse(array_filter(explode('/', $uri),
            function ($var) {
                return $var !== '' && $var !== '..';
            }));
        $_ACTION_LIST = array();
    }

    // Get the action
    $action = array_pop($_URI_LIST);
    if ($action === null)
        $action = $default;

    // Try to find a controller
    if ($action !== null) {
        array_push($_ACTION_LIST, $action);
        $path = implode('/', $_ACTION_LIST).'.php';
        _exec_if_exists(CONTROLLER_DIR.$path);
        if ($alternative !== null)
            foreach ($alternative as $pattern => $path)
                if (empty($regex) || preg_match($pattern, $REST_PATH))      //TODO: What's $REST_PATH?
                    _exec_if_exists(CONTROLLER_DIR.$path);
    }

    // TODO: Call 404 function
    header('HTTP/1.1 404 Not Found');
    exit('Not found');
}
