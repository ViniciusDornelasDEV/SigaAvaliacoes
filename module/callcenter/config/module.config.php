<?php
return array(
    'router' => array(
        'routes' => array(
            //listar avasiações para administrador do callcenter
            'administradorAvaliacoesCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Liberar avaliações
            'liberarAvaliacoesCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/liberaravaliacoes[/:page]',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'liberaravaliacoes',
                    ),
                ),
            ),
            //alterar avaliações
            'alterarAvaliacoesCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/alteraravaliacoes',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'alteraravaliacoes',
                    ),
                ),
            ),
            //deletar liberacao de avaliações
            'deletarLiberacaoCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/liberaravaliacoes/deletar[/:id]',
                    'constraints' => array(
                            'id'    =>  '[0-9]+',
                        ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'deletarliberacao',
                    ),
                ),
            ),
            //Gráficos
            'grafico' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/grafico',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'grafico',
                    ),
                ),
            ),
            'visualizarGrafico' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/grafico/visualizar[/:empresa][/:data]',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'visualizargrafico',
                    ),
                ),
            ),
            'graficoPersonalizado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/grafico/personalizado[/:limpar]',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'graficopersonalizado',
                    ),
                ),
            ),
            'visualizarGraficoPersonalizado' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/grafico/personalizado/visualizar',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'visualizargraficopersonalizado',
                    ),
                ),
            ),
            //listar avaliações para operador callcenter
            'listarAvaliacoesCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/avaliacoes/lista[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'listaravaliacoes',
                    ),
                ),
            ),
            //operador callcenter responde a avaliação
            'avaliacaoCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/avaliacao[/:dataAvaliacao]',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'avaliacao',
                    ),
                ),
            ),
            //visualizar avaliação respondida
            'visualizarAvaliacaoCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/avaliacoes/visualizar[/:idAvaliacao]',
                    'constraints' => array(
                        'idAvaliacao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'visualizaravaliacao',
                    ),
                ),
            ),
            'planilhaCallCenter' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/avaliacao/planilha',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'planilhacallcenter',
                    ),
                ),
            ),
            'planilhaCallCenter2' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/avaliacao/planilha2',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'planilhacallcenter2',
                    ),
                ),
            ),
            'listaAvaliacoesCallCenterAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/avaliacao/lista/administrador',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'listaavaliacoesadm',
                    ),
                ),
            ),

            'indexMetasAgendamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/lista/metasagentamento[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'index',
                    ),
                ),
            ),
            'novoMetasAgendamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/novo/metasagentamento',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'alterarMetasAgendamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/alterar/metasagentamento[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'editar',
                    ),
                ),
            ),
            'deletarMetasAgendamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/deletar/metasagentamento[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'deletar',
                    ),
                ),
            ),

            'indexMetasMensais' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/lista/metasagentamento/mensal[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'listametasmensais',
                    ),
                ),
            ),
            'novoMetaMensal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/novo/metasagentamento/mensal',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'novometamensal',
                    ),
                ),
            ),
            'alterarMetaMensal' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/alterar/metasagentamento/mensal[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'editarmetamensal',
                    ),
                ),
            ),
            'deletarMetamensal' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/deletar/metasagentamento/mensal[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'deletarmetamensal',
                    ),
                ),
            ),

            'replicarMetaMensal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/replicar/mensal',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Meta',
                        'action'     => 'replicarmensal',
                    ),
                ),
            ),

            'planilhaBancoDados' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/relatorio/bancodados',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'planilhabancodados',
                    ),
                ),
            ),

            'novaAvaliacaoAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/novo/avaliacao/administrador[/:data][/:empresa]',
                    'constraints' => array(
                        'empresa'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'novaavaliacaoadm',
                    ),
                ),
            ),

            'alterarAvaliacaoAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/callcenter/alterar/avaliacao/administrador[/:idAvaliacao]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'alteraravaliacaoadm',
                    ),
                ),
            ),

            /*'corrigirbugdatas' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/callcenter/corrigir/datas',
                    'defaults' => array(
                        'controller' => 'Callcenter\Controller\Avaliacao',
                        'action'     => 'corrigirbugdatas',
                    ),
                ),
            )*/
            
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Callcenter\Controller\Avaliacao' => 'Callcenter\Controller\AvaliacaoController',
            'Callcenter\Controller\Meta' => 'Callcenter\Controller\MetaController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/admincallcenter'           => __DIR__ . '/../view/layout/layoutadministrador.phtml',
            'layout/callcenter'           => __DIR__ . '/../view/layout/layoutcallcenter.phtml',
            'form/callcenter'             => __DIR__ . '/../view/partials/exibirFormCallcenter.phtml',
            'form/exibircallcenter'       => __DIR__ . '/../view/partials/visualizarFormCallcenter.phtml',
        ),
        'template_path_stack' => array( 
            __DIR__ . '/../view',
        ),
    ),
);