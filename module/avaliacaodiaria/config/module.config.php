<?php
return array(
    'router' => array(
        'routes' => array(
            //listar avasiações para administrador das avaliações diárias
            'administradorAvaliacoesDiarias' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Liberar avaliações
            'liberarAvaliacoesDiarias' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/liberaravaliacoes[/:page]',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'liberaravaliacoes',
                    ),
                ),
            ),
            //deletar liberacao de avaliações
            'deletarLiberacaoDiaria' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/liberaravaliacoes/deletar[/:id]',
                    'constraints' => array(
                            'id'    =>  '[0-9]+',
                        ),
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'deletarliberacao',
                    ),
                ),
            ),
            //Gráficos
            'graficoAvaliacoesDiarias' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/diaria/grafico',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'grafico',
                    ),
                ),
            ),
            'visualizarGraficoAvaliacoesDiarias' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/grafico/visualizar[/:empresa][/:data]',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'visualizargrafico',
                    ),
                ),
            ),
            'graficoPersonalizadoAvaliacoesDiarias' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/grafico/personalizado[/:limpar]',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'graficopersonalizado',
                    ),
                ),
            ),
            'visualizarGraficoPersonalizadoAvaliacoesDiarias' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/diaria/grafico/personalizado/visualizar',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'visualizargraficopersonalizado',
                    ),
                ),
            ),
            //listar avaliações para usuário que respondeu
            'listarAvaliacoesDiarias' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/avaliacoes/lista[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'listaravaliacoes',
                    ),
                ),
            ),
            //operador responde a avaliação
            'avaliacaoDiaria' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/avaliacao[/:dataAvaliacao]',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'avaliacao',
                    ),
                ),
            ),
            //visualizar avaliação respondida
            'visualizarAvaliacaoDiaria' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/avaliacoes/visualizar[/:idAvaliacao]',
                    'constraints' => array(
                        'idAvaliacao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'visualizaravaliacao',
                    ),
                ),
            ),
            'planilhaAvaliacaoDiaria' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/diaria/avaliacao/planilha',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'planilhaavaliacaodiaria',
                    ),
                ),
            ),
            'planilhaAvaliacaoDiaria2' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/diaria/avaliacao/planilha2',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'planilhaavaliacaodiaria2',
                    ),
                ),
            ),
            'listaAvaliacoesDiariasAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/diaria/avaliacao/lista/administrador',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'listaavaliacoesadm',
                    ),
                ),
            ),

            'planilhaBancoDadosAvaliacaoDiaria' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/diaria/relatorio/bancodados',
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'planilhabancodados',
                    ),
                ),
            ),

            'novaAvaliacaoDiariaAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/novo/avaliacao/administrador[/:data][/:empresa]',
                    'constraints' => array(
                        'empresa'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'novaavaliacaoadm',
                    ),
                ),
            ),

            'alterarAvaliacaoDiariaAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/diaria/alterar/avaliacao/administrador[/:idAvaliacao]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacaodiaria\Controller\Avaliacao',
                        'action'     => 'alteraravaliacaoadm',
                    ),
                ),
            ),
            
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Avaliacaodiaria\Controller\Avaliacao' => 'Avaliacaodiaria\Controller\AvaliacaoController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/admindiaria'           => __DIR__ . '/../view/layout/layoutadministrador.phtml',
            'layout/avaliacaodiaria'           => __DIR__ . '/../view/layout/layoutavaliacaodiaria.phtml',
            'form/avaliacaodiaria'             => __DIR__ . '/../view/partials/exibirFormAvaliacaoDiaria.phtml',
            'form/exibiravaliacaodiaria'       => __DIR__ . '/../view/partials/visualizarAvaliacaoDiaria.phtml',
        ),
        'template_path_stack' => array( 
            __DIR__ . '/../view',
        ),
    ),
);