<?php

require_once __DIR__.'/../include/init.php';
require_once INCLUDE_DIR.'func.rest.php';

// Check whether the requested URI is an old-style .asp URI. If it is,
// redirect to a corresponding new URI
if (stripos($_SERVER['REQUEST_URI'], '.asp') !== false) {
    if (!isset($_SERVER['HTTP_USER_AGENT']) ||
        stripos($_SERVER['HTTP_USER_AGENT'], 'Baiduspider') === false &&
            stripos($_SERVER['HTTP_USER_AGENT'], 'Sogou web spider') === false &&
            stripos($_SERVER['HTTP_USER_AGENT'], 'Sosospider') === false
    )
        $prefix = 'https://';
    else
        $prefix = 'http://';

    header('HTTP/1.1 301 Moved Permanently');

    $uri  = $_SERVER['REQUEST_URI'];
    $host = $_SERVER['HTTP_HOST'];

    if (stripos($uri, '/problem_show.asp') !== false)
        header('Location: '.$prefix.SVR_HOST.'/p/'.$_GET['id']);
    else if (stripos($uri, '/user_show.asp') !== false)
        header('Location: '.$prefix.SVR_HOST.'/user/'.$_GET['id']);
    else if (stripos($uri, '/problem_discuss.asp') !== false)
        header('Location: '.$prefix.SVR_HOST.'/p/'.$_GET['id']);
    else if (stripos($uri, '/problem_discuss_show.asp') !== false)
        header('Location: '.$prefix.SVR_HOST.'/p/'.$_GET['id']);
    else if (stripos($uri, '/problem2.asp') !== false)
        header('Location: '.$prefix.SVR_HOST.'/p');
    else
        header('Location: '.$prefix.SVR_HOST);

    return;
}

\rest\uri_route('index');
