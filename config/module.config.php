<?php
return array(
    'router' => array(
        'routes' => include 'routes.config.php'
    ),
    'acl' => array(
        'roles' => include 'acl-roles-config.php'
    ),
    'enable_rest_client_ssl_verification' => true,
    'default_configuration' => array(
        'first_year' => 2007,
        'last_year' => 2020
    ),
    'service_manager' => include 'services-config.php',
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    ),
    'controllers' => include 'controllers-config.php',
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy'
        ),
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'zfcuser/user/login' => __DIR__ . '/../view/user/login.phtml',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            'zfcuser' => __DIR__ . '/../view',
            __DIR__ . '/../view'
        )
    ),
    'view_helpers' => include 'view-helpers-config.php',
    'data_transfer_agents' => include 'data-transfer-agents.php',
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'rest_process_force_execution' => array(
                    'options' => array(
                        'route' => 'execute <mode> <type> <endpoint> [<annee>]',
                        'verb' => 'post',
                        'defaults' => array(
                            'controller' => 'Minibus\Controller\ExecutionConsole',
                            'action' => 'execute'
                        ),
                        'constraints' => array(
                            'mode' => '/[a-z_]+/',
                            'type' => '/[a-z_]+/',
                            'endpoint' => '/[a-z_]+/',
                            'annee' => '/[0-9]+/'
                        )
                    )
                )
            )
        )
    )
    ,
    'doctrine' => include 'doctrine-config.php'
);
