<?php
return array(
    'router' => array(
        'routes' => array(
            'empresa' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa[/:page]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Empresa',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Nova empresa
            'empresaNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/novo[/:id]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                	),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Empresa',
                        'action'     => 'novo',
                    ),
                ),
            ),
            //Alterar empresa
            'empresaAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/alterar[/:id]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                	),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Empresa',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            //Alterar empresa
            'replicarArquivo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/arquivo/replicar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Empresa',
                        'action'     => 'replicararquivo',
                    ),
                ),
            ),
            //Deletar empresa
            'empresaDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/deletarempresa[/:id]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                	),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Empresa',
                        'action'     => 'deletarempresa',
                    ),
                ),
            ),
            //arquivo deletar
            'arquivoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/deletararquivo[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Empresa',
                        'action'     => 'deletararquivo',
                    ),
                ),
            ),
            //download de arquivo
            'downloadArquivo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/downloadarquivo[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Empresa',
                        'action'     => 'downloadarquivo',
                    ),
                ),
            ),

            //Login
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/login',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'login',
                    ),
                ),
            ),

            //Login
            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/logout',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'logout',
                    ),
                ),
            ),

            'usuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario[/:page]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Novo usuario
            'usuarioNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/usuario/novo',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'novo',
                    ),
                ),
            ),
            //Alterar usuario
            'usuarioAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/alterar[/:id][/:idMedico]',
                    'constraints' => array(
                        'id'            => '[0-9]+',
                        'idMedico'      => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            //Deletar usuario
            'usuarioDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/deletarusuario[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'deletarusuario',
                    ),
                ),
            ),
            //desativar médico
            'medicoDesativar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/desativarmedico[/:id][/:diretor]',
                    'constraints' => array(
                        'id'        => '[0-9]+',
                        'diretor'   => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'medicodesativar',
                    ),
                ),
            ),
            'medicoAtivar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/ativarmedico[/:id][/:diretor]',
                    'constraints' => array(
                        'id'        => '[0-9]+',
                        'diretor'   => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'medicoativar',
                    ),
                ),
            ),
            //desativar operador
            'operadorDesativar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/desativaroperador[/:id][/:diretor]',
                    'constraints' => array(
                        'id'        => '[0-9]+',
                        'diretor'   => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'operadordesativar',
                    ),
                ),
            ),
            //desvincular empresa
            'empresaAuxiliarDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/auxiliar/deletarempresa[/:id][/:usuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'deletarempresaauxiliar',
                    ),
                ),
            ),
            //desvincular empresas
            'empresasAuxiliarDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/auxiliar/deletarempresas[/:usuario]',
                    'constraints' => array(
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'deletarempresasauxiliar',
                    ),
                ),
            ),

            //Desvincular aba
            'abaDesvincular' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/desvincularaba[/:aba][/:id]',
                    'constraints' => array(
                        'aba'     => '[0-9]+',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'desvincularaba',
                    ),
                ),
            ),

            'tipousuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario[/:page]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Novo tipousuario
            'tipousuarioNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/novo[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'novo',
                    ),
                ),
            ),
            //Alterar tipousuario
            'tipousuarioAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/alterar[/:id][/:recurso]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            //Deletar tipousuario
            'tipousuarioDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/deletartipousuario[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'deletartipousuario',
                    ),
                ),
            ),

            //Desvincular recurso
            'recursoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/deletarrecurso[/:id][/:tipousuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'deletarrecurso',
                    ),
                ),
            ),
            //Alterar senha
            'alterarSenha' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/alterarsenha',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'alterarsenha',
                    ),
                ),
            ),
            'recuperarSenha' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/recuperarsenha',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'recuperarsenha',
                    ),
                ),
            ),

            'descricaoRecurso' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/descricaorecurso',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'descricaorecurso',
                    ),
                ),
            ),

            'mudarEmpresa' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/alteraempresa',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'mudarempresa',
                    ),
                ),
            ),

        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Usuario\Controller\Empresa' => 'Usuario\Controller\EmpresaController',
            'Usuario\Controller\Usuario' => 'Usuario\Controller\UsuarioController',
            'Usuario\Controller\Tipousuario' => 'Usuario\Controller\TipousuarioController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'form/login'        => __DIR__ . '/../view/partials/formLogin.phtml',
            'form/recuperaSenha'        => __DIR__ . '/../view/partials/formRecuperaSenha.phtml',
            'layout/login'           => __DIR__ . '/../view/layout/layoutlogin.phtml'
        ),
    ),
);