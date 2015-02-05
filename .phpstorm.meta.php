<?php
namespace PHPSTORM_META {                                                 // we want to avoid the pollution

    /** @noinspection PhpUnusedLocalVariableInspection */                 // just to have a green code below
    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    $STATIC_METHOD_TYPES = [                                              // we make sections for scopes
        \VJ\Core\Application::get('') => [
            'dispatcher' instanceof \FastRoute\Dispatcher,
            'i18n' instanceof \Symfony\Component\Translation\Translator,
            'request' instanceof \VJ\Core\Request,
            'response' instanceof \VJ\Core\Response,
            'templating' instanceof \Twig_Environment,
            'mongo_client' instanceof \MongoClient,
            'mongodb' instanceof \MongoDB,
            'log' instanceof \Monolog\Logger,
            'event' instanceof \Evenement\EventEmitter,
            'random_factory' instanceof \RandomLib\Factory,
            'random' instanceof \RandomLib\Generator,
            'random_secure' instanceof \RandomLib\Generator,
            'session_storage' instanceof \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface,
            'session' instanceof \Symfony\Component\HttpFoundation\Session\Session
        ]
    ];

}