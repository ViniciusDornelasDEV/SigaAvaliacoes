<?php
return array(
    'router' => array(
        'routes' => array(
            'simulador' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/medicos/simulador',
                    'defaults' => array(
                        'controller' => 'Remuneracaomedica\Controller\Simulador',
                        'action'     => 'simulador',
                    ),
                ),
            ),

            'relacionamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/medicos/relacionamento',
                    'defaults' => array(
                        'controller' => 'Remuneracaomedica\Controller\Simulador',
                        'action'     => 'relacionamento',
                    ),
                ),
            ),
            
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Remuneracaomedica\Controller\Simulador' => 'Remuneracaomedica\Controller\SimuladorController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/remuneracaomedica'           => __DIR__ . '/../view/layout/layoutremuneracao.phtml',
        ),
        'template_path_stack' => array( 
            __DIR__ . '/../view',
        ),
    ),
);