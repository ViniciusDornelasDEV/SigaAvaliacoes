<?php
return array(
    'router' => array(
        'routes' => array(
            'avaliacoesaudicaonovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/audicao[/:page]',
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'index',
                    ),
                ),
            ),
            'avaliacaoAgendamentoAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/agendamento[/:id][/:empresa]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'empresa'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'agendamento',
                    ),
                ),
            ),
            'avaliacaoComercialAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/comercial[/:id][/:empresa]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'empresa'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'comercial',
                    ),
                ),
            ),
            'avaliacaoProcessoAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/processo[/:id][/:empresa]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'empresa'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'processo',
                    ),
                ),
            ),
            'avaliacaoQualidadeAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/qualidade[/:id][/:empresa]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'empresa'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'qualidade',
                    ),
                ),
            ),
            'avaliacaoSegurancaAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/seguranca[/:id][/:empresa]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'empresa'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'seguranca',
                    ),
                ),
            ),
            'avaliacaoAtaAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/ata[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'empresa'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'ata',
                    ),
                ),
            ),
            'avaliacaoQualitativaAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/qualitativa[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'qualitativamedico',
                    ),
                ),
            ),
            'avaliacaoPidAudicao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/novo/pid[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'pidmedico',
                    ),
                ),
            ),

            'responderAvaliacao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/audicao/responder/avaliacao[/:aba][/:empresa][/:ano][/:mes]',
                    'constraints' => array(
                        'aba'     => '[0-9]+',
                        'empresa' => '[0-9]+',
                        'ano'     => '[0-9]+',
                        'mes'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Audicao\Controller\Audicao',
                        'action'     => 'responderavaliacao',
                    ),
                ),
            ),
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Audicao\Controller\Audicao'              => 'Audicao\Controller\AudicaoController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'form/audicao'             => __DIR__ . '/../view/partials/formAudicao.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    )
);
?>