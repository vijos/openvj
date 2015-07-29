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
            'redis' instanceof \Redis,
            'mongo_client' instanceof \MongoClient,
            'mongodb' instanceof \MongoDB,
            'es_client' instanceof \Elastica\Client,
            'es' instanceof \Elastica\Index,
            'log' instanceof \Monolog\Logger,
            'event' instanceof \Symfony\Component\EventDispatcher\EventDispatcher,
            'random_factory' instanceof \RandomLib\Factory,
            'random' instanceof \RandomLib\Generator,
            'random_secure' instanceof \RandomLib\Generator,
            'session_storage' instanceof \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface,

            'vj_redirection_service' instanceof \VJ\EventListener\VJRedirectionService,
            'https_redirection_service' instanceof \VJ\EventListener\HttpsRedirectionService,
            'login_log_service' instanceof \VJ\EventListener\LoginLogService,
            'vj2_credential_upgrade_service' instanceof \VJ\EventListener\VJCredentialUpgradeService,

            'keyword_filter' instanceof \VJ\Security\KeywordFilter,
            'token_manager' instanceof \VJ\Security\TokenManager,
            'message_signer' instanceof \VJ\Security\MessageSigner,
            'user_session' instanceof \VJ\User\UserSession,
            'domain_manager' instanceof \VJ\User\DomainManager,
            'password_encoder' instanceof \VJ\User\PasswordEncoder,
            'user_credential' instanceof \VJ\User\UserCredential,
            'user_manager' instanceof \VJ\User\UserManager,
            'bgservice_mailing_provider' instanceof \VJ\Mail\BgServiceMailingProvider,
            'mail_sender' instanceof \VJ\Mail\Sender,
        ]
    ];

}