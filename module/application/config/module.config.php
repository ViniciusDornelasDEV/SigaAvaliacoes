<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),

            'cidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/cidade',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Ajax',
                        'action'     => 'cidade',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Ajax' => 'Application\Controller\AjaxController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/limpo'           => __DIR__ . '/../view/layout/layoutlimpo.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'form/generic'              => __DIR__ . '/../view/partials/form.phtml',
            'form/umacoluna'              => __DIR__ . '/../view/partials/formUmaColuna.phtml',
            'view/paginator'              => __DIR__ . '/../view/partials/paginator.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
      'invokables' => array(
         'status'      => 'Application\Helper\Status',
         'tipounidade' => 'Application\Helper\Tipounidade',
         'converterData' => 'Application\Helper\Converterdata',
         'caracterVazio' => 'Application\Helper\Caractervazio',
         'exibirMonetario' => 'Application\Helper\Exibirmonetario',
         'numeroMes' => 'Application\Helper\Numeromes',
         'defineStatus' => 'Application\Helper\Definestatus',
         'abaRespondida' => 'Application\Helper\Abarespondida'

      ),
   ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'siga',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            array(
                'Zend\Session\Validator\RemoteAddr',
                'Zend\Session\Validator\HttpUserAgent',
            ),
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'controleAcesso' => 'Application\Plugin\ControleAcesso',
            'funcoes' => 'Application\Plugin\Funcoes', 
            'definirDias' => 'Application\Plugin\DefinirDias', 
            
        )
    ),
);