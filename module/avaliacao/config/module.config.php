<?php
return array(
    'router' => array(
        'routes' => array(
            'avaliacoesEmpresa' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/empresa[/:page]',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'index',
                    ),
                ),
            ),
            'listaAvaliacoesAberto' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/abertas',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'listaavaliacoesaberto',
                    ),
                ),
            ),
            'avaliacaoAgendamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/agendamento[/:mes][/:ano]',
                    'constraints' => array(
                        'mes'     => '[0-9]+',
                        'ano'     => '[0-9]+'
                	),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'agendamento',
                    ),
                ),
            ),
            'avaliacaoComercial' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/comercial[/:mes][/:ano]',
                    'constraints' => array(
                        'mes'     => '[0-9]+',
                        'ano'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'comercial',
                    ),
                ),
            ),
            'avaliacaoProcesso' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/processo[/:mes][/:ano]',
                    'constraints' => array(
                        'mes'     => '[0-9]+',
                        'ano'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'processo',
                    ),
                ),
            ),
            'avaliacaoQualidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/qualidade[/:mes][/:ano]',
                    'constraints' => array(
                        'mes'     => '[0-9]+',
                        'ano'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'qualidade',
                    ),
                ),
            ),
            'avaliacaoSeguranca' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/seguranca[/:mes][/:ano]',
                    'constraints' => array(
                        'mes'     => '[0-9]+',
                        'ano'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'seguranca',
                    ),
                ),
            ),
            'avaliacaoAta' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/ata[/:mes][/:ano]',
                    'constraints' => array(
                        'mes'     => '[0-9]+',
                        'ano'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'ata',
                    ),
                ),
            ),
            'avaliacaoArquivos' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/arquivos[/:ano][/:mes][/:empresa][/:redirecionar][/:avaliacao][/:menu]',
                    'constraints' => array(
                        'empresa'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'arquivos',
                    ),
                ),
            ),
            'download' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/download[/:id][/:campo][/:aba]',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'download',
                    ),
                ),
            ),
            'avaliacaoAgendamentoVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/agendamento/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'visualizaragendamento',
                    ),
                ),
            ),
            'avaliacaoComercialVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/comercial/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'visualizarcomercial',
                    ),
                ),
            ),
            'avaliacaoProcessoVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/processo/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'visualizarprocesso',
                    ),
                ),
            ),
            'avaliacaoQualidadeVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/qualidade/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'visualizarqualidade',
                    ),
                ),
            ),
            'avaliacaoSegurancaVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/seguranca/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'visualizarseguranca',
                    ),
                ),
            ),
            'avaliacaoAtaVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/ata/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'visualizarata',
                    ),
                ),
            ),
            'listaAvaliacaoAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/admin[/:tipo][/:page]',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'index',
                    ),
                ),
            ),
            'avaliacaoAgendamentoAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/agendamento/adm[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizaragendamento',
                    ),
                ),
            ),
            'avaliacaoComercialAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/comercial/adm[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizarcomercial',
                    ),
                ),
            ),
            'avaliacaoProcessoAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/processo/adm[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizarprocesso',
                    ),
                ),
            ),
            'avaliacaoQualidadeAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/qualidade/adm[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizarqualidade',
                    ),
                ),
            ),
            'avaliacaoSegurancaAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/seguranca/adm[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizarseguranca',
                    ),
                ),
            ),
            'avaliacaoAtaAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/ata/adm[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizarata',
                    ),
                ),
            ),
            'excluirAvaliacaoAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/adm/excluir[/:id][/:aba]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'excluiravaliacao',
                    ),
                ),
            ),
            'arquivosVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/arquivos/visualizar[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizararquivos',
                    ),
                ),
            ),
            'avaliacaoQualitativaAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/qualitativa/adm[/:id]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizarqualitativa',
                    ),
                ),
            ),
            'avaliacaoPidAdm' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/novo/pid/adm[/:medico][/:periodo][/:qualitativa]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'visualizarpid',
                    ),
                ),
            ),
            'exportarAgendamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/exportar/agendamento[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'exportaragendamento',
                    ),
                ),
            ),
            'exportarProcesso' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/exportar/processo[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'exportarprocesso',
                    ),
                ),
            ),
            'exportarQualidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/exportar/qualidade[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'exportarqualidade',
                    ),
                ),
            ),
            'exportarSeguranca' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/exportar/seguranca[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'exportarseguranca',
                    ),
                ),
            ),
            'planilhaCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/exportar/avaliacao[/:mes][/:ano][/:empresa]',
                    'constraints' => array(
                        'mes'     => '[0-9]+',
                        'ano'     => '[0-9]+',
                        'empresa'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'planilhacliente',
                    ),
                ),
            ),
            'planilhaMedico' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/exportar/avaliacao/medico[/:qualitativa][/:pid]',
                    'constraints' => array(
                        'qualitativa'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaoadm',
                        'action'     => 'planilhamedico',
                    ),
                ),
            ),
            'downloadPlanilha' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/exportar/avaliacao/download',
                    'constraints' => array(
                      
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacao',
                        'action'     => 'downloadplanilha',
                    ),
                ),
            ),
            'medicoIndex' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/medico',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaomedico',
                        'action'     => 'index',
                    ),
                ),
            ),
            'medicoAvaliacaoQualitativa' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/medico/novo/avaliacao/qualitativa[/:medico][/:periodo]',
                    'constraints' => array(
                        'medico'     => '[0-9]+',
                        'periodo'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaomedico',
                        'action'     => 'medicoavaliacaoqualitativa',
                    ),
                ),
            ),
            'medicoPid' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/medico/novo/pid[/:medico][/:periodo]',
                    'constraints' => array(
                        'medico'     => '[0-9]+',
                        'periodo'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaomedico',
                        'action'     => 'medicopid',
                    ),
                ),
            ),
            'imprimirAvaliacaoQualitativa' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/medico/imprimir/qualitativa[/:medico][/:periodo][/:idAvaliacao]',
                    'constraints' => array(
                        'medico'     => '[0-9]+',
                        'periodo'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaomedico',
                        'action'     => 'imprimiravaliacaoqualitativa',
                    ),
                ),
            ),
            'imprimirAvaliacaoPid' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/medico/imprimir/pid[/:medico][/:periodo][/:idAvaliacao]',
                    'constraints' => array(
                        'idAvaliacao'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaomedico',
                        'action'     => 'imprimiravaliacaopid',
                    ),
                ),
            ),


            'indexFinalizadas' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/medico/finalizadas[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaomedico',
                        'action'     => 'indexfinalizadas',
                    ),
                ),
            ),
            
            'inserirAvaliacoes' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/liberaravaliacoes',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Configuraravaliacao',
                        'action'     => 'inseriravaliacoes',
                    ),
                ),
            ),
            'alterarLiberacao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/alteraravaliacoes[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Configuraravaliacao',
                        'action'     => 'alterarliberacao',
                    ),
                ),
            ),
            'listaLiberacoes' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/listarliberacoes[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Configuraravaliacao',
                        'action'     => 'listaliberacoes',
                    ),
                ),
            ),
            'liberacaoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/liberacao/deletar[/:id]',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Configuraravaliacao',
                        'action'     => 'liberacaodeletar',
                    ),
                ),
            ),
            'personalizarAvaliacao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/personalizar[/:empresa][/:aba]',
                    'constraints' => array(
                        'empresa'     => '[0-9]+',
                        'aba'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Configuraravaliacao',
                        'action'     => 'personalizaravaliacao',
                    ),
                ),
            ),
            'listarAvaliacoesMedico' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliacao/medico/listaravaliacoes',
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Avaliacaomedico',
                        'action'     => 'listaravaliacoesmedico',
                    ),
                ),
            ),

            /* HISTÓRICO DE AVALIAÇÔES PASSADAS */
            'historicoAgendamentoVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/historico/agendamento/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Historicoavaliacao',
                        'action'     => 'visualizaragendamento',
                    ),
                ),
            ),
            'historicoComercialVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/historico/comercial/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Historicoavaliacao',
                        'action'     => 'visualizarcomercial',
                    ),
                ),
            ),
            'historicoProcessoVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/historico/processo/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Historicoavaliacao',
                        'action'     => 'visualizarprocesso',
                    ),
                ),
            ),
            'historicoQualidadeVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/historico/qualidade/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Historicoavaliacao',
                        'action'     => 'visualizarqualidade',
                    ),
                ),
            ),
            'historicoSegurancaVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/historico/seguranca/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Historicoavaliacao',
                        'action'     => 'visualizarseguranca',
                    ),
                ),
            ),
            'historicoAtaVisualizar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliacao/historico/ata/visualizar[/:mes][/:ano]',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Avaliacao\Controller\Historicoavaliacao',
                        'action'     => 'visualizarata',
                    ),
                ),
            ),
            
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Avaliacao\Controller\Avaliacao'            => 'Avaliacao\Controller\AvaliacaoController',
            'Avaliacao\Controller\Avaliacaoadm'         => 'Avaliacao\Controller\AvaliacaoadmController',
            'Avaliacao\Controller\Default'              => 'Avaliacao\Controller\DefaultController',
            'Avaliacao\Controller\Avaliacaomedico'      =>  'Avaliacao\Controller\AvaliacaomedicoController',
            'Avaliacao\Controller\Configuraravaliacao'  => 'Avaliacao\Controller\ConfiguraravaliacaoController',
            'Avaliacao\Controller\Historicoavaliacao'   => 'Avaliacao\Controller\HistoricoavaliacaoController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/avaliacaocliente'           => __DIR__ . '/../view/layout/avaliacaocliente.phtml',
            'layout/avaliacaoindex'           => __DIR__ . '/../view/layout/avaliacaoindex.phtml',
            'layout/medico'           => __DIR__ . '/../view/layout/medico.phtml',
            'form/personalizarcampos'              => __DIR__ . '/../view/partials/formPersonalizarCampos.phtml',
            'form/avaliacao'             => __DIR__ . '/../view/partials/exibirFormAvaliacao.phtml',
            'form/auditado'             => __DIR__ . '/../view/partials/exibirFormAuditado.phtml',
            'form/medicoauditado'             => __DIR__ . '/../view/partials/exibirFormMedicoAuditado.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);