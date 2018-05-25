<?php

namespace Usuario\Controller;

use Application\Controller\BaseController;
use Usuario\Form\Login as loginForm;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter as AuthAdapter;
use Zend\Crypt\Password\Bcrypt;
use Zend\Authentication\Result;
use Zend\Session\SessionManager;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Session\Container;
use Usuario\Form\Usuario as usuarioForm;
use Usuario\Form\AlterarUsuario as alterarUsuarioForm;
use Usuario\Form\PesquisaUsuario as pesquisaForm;
use Usuario\Form\AlterarSenha as alterarSenhaForm;
use Usuario\Form\RecuperarSenha as novaSenhaForm;
use Usuario\Form\Aba as abaForm;
use Usuario\Form\MudaEmpresa as mudaEmpresaForm;
use Usuario\Form\VincularEmpresas as vincularEmpresaForm;
use Usuario\Form\Medico as medicoForm;
use Callcenter\Form\CallCenter as operadorForm;
use Avaliacaodiaria\Form\Operador as operadorDiariaForm;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Mail;

class UsuarioController extends AbstractActionController
{
    public function loginAction()
    {
        $this->layout('layout/login');
        $formMensal = new loginForm('formMensal');
        $formDiaria = new loginForm('formDiaria');
        $formMensal = new loginForm('formCallCenter');
        $formMensal = new loginForm('formMedicos');

        //Log in
        $request = $this->getRequest();
        $post = $request->getPost();
        
        if(!isset($post->login)) {
            if(isset($_POST['login'])){
                $post = $_POST;
            }else{
                //header("Location: http://www.rstconsultoria.com.br/");
                //die();
            }
        }
        
        if ($request->isPost()) {
            //definir o form
            $form = $formMensal;
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();

                // Configure the instance with constructor parameters...

                $authAdapter = new AuthAdapter($this->getServiceLocator()
                                    ->get('db_adapter_main'), 'tb_usuario', 'login', 'senha', 
                                    function($dbCredential, $requestCredential) {
                                        $bcrypt = new Bcrypt();
                                        return $bcrypt->verify($requestCredential, $dbCredential);
                });
                
                //apenas ativo = S
                $select = $authAdapter->getDbSelect();
                $select->where('ativo = "S"');

                $authAdapter
                        ->setTableName('tb_usuario')
                        ->setIdentityColumn('login')
                        ->setCredentialColumn('senha');

                $authAdapter
                        ->setIdentity($data['login'])
                        ->setCredential($data['password']);    

                $result = $authAdapter->authenticate()->getCode();    
                
                $session = $this->getServiceLocator()->get('session'); 
               
                
                if ($result === Result::SUCCESS) {
                    //remember me?
                    if(isset($post->remember_me) && $post->remember_me == 1) {                     
                        $defaultNamespace = new SessionManager();
                        $defaultNamespace->rememberMe();
                    }            
                    
                    $user = (array)$authAdapter->getResultRowObject();

                    //se logou na caixa erada voltar para login
                    if(isset($post['qualidade_mensal'])){
                        if(!in_array($user['id_usuario_tipo'], array(4, 5, 6, 7))){
                            return $this->redirect()->toRoute('logout');
                        }
                    }

                    if(isset($post['qualidade_diaria'])){
                        if(!in_array($user['id_usuario_tipo'], array(12, 13))){
                            return $this->redirect()->toRoute('logout');
                        }
                    }

                    if(isset($post['callcenter'])){
                        if(!in_array($user['id_usuario_tipo'], array(10, 11))){
                            return $this->redirect()->toRoute('logout');
                        }
                    }

                    if(isset($post['medicos']) || isset($post['simulador'])){
                        if(!in_array($user['id_usuario_tipo'], array(8, 9))){
                            return $this->redirect()->toRoute('logout');
                        }
                    }



                    $session->write($user);                                       

                    //Create acl config
                    $sessao = new Container();
                    $sessao->setExpirationSeconds(7200);
                    $sessao->acl = $this->criarAutorizacao();
                    
                    switch ($user['id_usuario_tipo']) {
                        case 5:
                            //enviar empresas para avaliações
                            return $this->redirect()->toRoute('avaliacoesEmpresa');
                        case 6:
                            //auditor
                            return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                        case 7:
                            //Auxiliar
                            return $this->redirect()->toRoute('listaAvaliacaoAdm', array('tipo' => 'C'));
                        case 8:
                            //Diretor médico
                            if(isset($post['simulador'])){
                                return $this->redirect()->toRoute('simulador');    
                            }
                            return $this->redirect()->toRoute('medicoIndex');
                        case 9:
                            //médico
                            //verificar se médico está em aberto
                            $medico = $this->getServiceLocator()->get('Medico')->getRecord($user['medico']);
                            if($medico->ativo != 'S'){
                                return $this->redirect()->toRoute('logout');
                            }
                            return $this->redirect()->toRoute('listarAvaliacoesMedico');
                        case 10:
                            return $this->redirect()->toRoute('administradorAvaliacoesCallCenter');
                        case 11:
                            return $this->redirect()->toRoute('avaliacaoCallCenter');
                        case 12:

                            return $this->redirect()->toRoute('administradorAvaliacoesDiarias');
                        case 13:
                            return $this->redirect()->toRoute('avaliacaoDiaria');
                        default:
                            return $this->redirect()->toRoute('home');
                            break;
                    }
                } else {
                	//form invalido
                    $session->clear();
                    //$this->flashMessenger()->addWarningMessage('Login ou senha inválidos!');
                    return $this->redirect()->toRoute('login');
                }
            }
        }    

        $formMensal = new loginForm('formMensal', 'qualidade_mensal', '#3c763d');
        $formDiaria = new loginForm('formDiaria', 'qualidade_diaria', '0000');
        $formCallcenter = new loginForm('formCallCenter', 'callcenter', '0000');
        $formMedicos = new loginForm('formMedicos', 'medicos', '0000');    

        return new ViewModel(array(
                                'formMensal'        => $formMensal,
                                'formDiaria'        => $formDiaria,
                                'formCallcenter'    => $formCallcenter,
                                'formMedicos'       => $formMedicos
                                ));

    }

    public function logoutAction() {
        ///die('aqui!');
        $session = $this->getServiceLocator()->get('session');  
        $defaultNamespace = new SessionManager();
        $defaultNamespace->destroy();
        $session->clear();
        return $this->redirect()->toRoute('login');
    }

    public function alterarsenhaAction() {
        $form = new alterarSenhaForm('frmUsuario');
        //Pegar usuário logado
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuario = $this->getServiceLocator()->get('session')->read();

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $form->setData($dados);
            if($form->isValid()){
                $bcrypt = new bcrypt();                

                if(!$bcrypt->verify($dados['senha_atual'], $usuario['senha'])){
                    $this->flashMessenger()->addWarningMessage('Senha atual não confere!');
                    return $this->redirect()->toRoute('alterarSenha');
                }
                //alterar senha
                $usuario['senha'] = $bcrypt->create($dados['senha']);
                if($serviceUsuario->update($usuario, array('id' => $usuario['id']))){
                    $this->flashMessenger()->addSuccessMessage('Senha alterada com sucesso!');  
                    return $this->redirect()->toRoute('logout');
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao alterar senha!');
                    return $this->redirect()->toRoute('alterarSenha');
                }
                
            }
        }

        if($usuario['id_usuario_tipo'] == 11 || $usuario['id_usuario_tipo'] == 13){
            $this->layout('layout/limpo');
        }
        return new ViewModel(array('form' => $form));
    }

    public function recuperarsenhaAction(){
        $this->layout('layout/login');
        $form = new novaSenhaForm('frmRecuperaSenha');
        
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $form->setData($dados);
            if($form->isValid()){
                $bcrypt = new bcrypt();                
                //alterar senha
                $serviceUsuario = $this->getServiceLocator()->get('Usuario');
                $novaSenha = 'si'.date('s+H()i');
                $usuario = array('senha' => $bcrypt->create($novaSenha));

                if($serviceUsuario->update($usuario, array('login' => $dados->login))){
                    $this->flashMessenger()->addSuccessMessage('Verifique a nova senha em sua conta de e-mail!');  
                    $mailer = $this->getServiceLocator()->get('mailer');
                    $mailer->mailUser($dados->login, 'Recuperar senha', 'Sua nova senha de acesso ao sistema é '.$novaSenha);
                    return $this->redirect()->toRoute('login');
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao recuperar senha!');
                    return $this->redirect()->toRoute('recuperarSenha');
                }

                
            }
            
        }
        
        

        return new ViewModel(array('form' => $form));
    }

    private function criarAutorizacao() {
        //pesquisar perfil de usuário
        $serviceUsuario = $this->getServiceLocator()->get('UsuarioTipo');
        $perfilUsuario = $serviceUsuario->getRecord($serviceUsuario->getIdentity('id_usuario_tipo'));
        
        //criando papel do usuário
        $acl = new Acl();
        $papel = new Role($perfilUsuario['perfil']);
        $acl->addRole($papel);

        //definindo recursos existentes no sistema
        $serviceRecurso = $this->getServiceLocator()->get('Recurso');
        $recursos = $serviceRecurso->fetchAll();
        foreach ($recursos as $resource) {
            $acl->addResource(new Resource($resource->nome));
        }

        //Adicionar permissões
        $recursosUsuario = $serviceRecurso->getRecursosByTipoUsuario(array('usuario_tipo' => $perfilUsuario['id']));
        foreach ($recursosUsuario as $resource) {
            $acl->allow($perfilUsuario['perfil'], $resource->nome);
        }
        return $acl;
    }

    public function indexAction(){
        $formPesquisa = new pesquisaForm('frmPesquisa', $this->getServiceLocator());
        $dados = false;
        $sessao = new Container();

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                //redir para mesma página
                unset($sessao->parametros);
                return $this->redirect()->toRoute('usuario');
            }else{
                $sessao->parametros = $dados;
                $formPesquisa->setData($dados);
            }
        }

        $serviceUsuario = $this->getServiceLocator()->get('Usuario');

        $usuarios = $serviceUsuario->getUsuariosByParams($sessao->parametros);

        $Paginator = new Paginator(new ArrayAdapter($usuarios->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        return new ViewModel(array(
                                'usuarios'      => $Paginator, 
                                'formPesquisa'   => $formPesquisa,
                            ));
    }

    public function novoAction(){
        $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator());
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $serviceUsuario = $this->getServiceLocator()->get('Usuario');
            
            //validar form
            $formUsuario->setData($dados);

            if($dados['id_usuario_tipo'] == 9){
                $this->flashMessenger()->addWarningMessage('O médico deve ser inserido junto ao diretor responsável!');
                return $this->redirect()->toRoute('usuarioNovo');
            }

            if($dados['id_usuario_tipo'] == 11){
                $this->flashMessenger()->addWarningMessage('O operador de call center deve ser inserido junto ao administrador responsável!');
                return $this->redirect()->toRoute('usuarioNovo');
            }

            if($dados['id_usuario_tipo'] == 13){
                $this->flashMessenger()->addWarningMessage('O operador de avaliações diárias deve ser inserido junto ao administrador responsável!');
                return $this->redirect()->toRoute('usuarioNovo');
            }            

            if($formUsuario->isValid()){  
                $bcrypt = new Bcrypt();
                $dados = $formUsuario->getData();
                $dados['senha'] = $bcrypt->create($dados['senha']);

                $result = $serviceUsuario->insert($dados);
                if($result){
                    
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');                
                    return $this->redirecionar($result);
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir usuário!');
                }
            }

        }

        return new ViewModel(array('formUsuario' => $formUsuario));
    }


    public function alterarAction(){
        //Pesquisar cliente
        $idUsuario = $this->params()->fromRoute('id');
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuario = $serviceUsuario->getRecordFromArray(array('id' => $idUsuario));

        //Popular form
        $formUsuario = new alterarUsuarioForm('frmUsuario', $this->getServiceLocator());
        unset($usuario['senha']);
        $formUsuario->setData($usuario);
        
        $serviceAbasUsuario = $this->getServiceLocator()->get('AbasUsuario');
        //Vincular empresa quando usuário for do tipo auxiliar
        $formAuxiliarEmpresa = false;
        $auxiliarEmpresas = false;
        $empresasAtivas = false;
        if($usuario->id_usuario_tipo == 7){
            $formAuxiliarEmpresa = new vincularEmpresaForm('formEmpresaAuxiliar', $this->getServiceLocator());

            //pesquisar empresas vinculadas
            $serviceAuxiliarEmpresas = $this->getServiceLocator()->get('AuxiliarEmpresas');
            $auxiliarEmpresas = $serviceAuxiliarEmpresas->getEmpresasByUsuario($usuario->id);

            //pesquisar empresas ativas
            $empresasAtivas = $this->getServiceLocator()->get('Empresa')->getRecords('S', 'ativo');
        }

        //verificar se usuário é do tipo diretor médico, se for abrir form para vincular médicos
        $medicos = false;
        $formMedico = false;
        if($usuario->id_usuario_tipo == 8){
            $formMedico = new medicoForm('formMedicos', $this->getServiceLocator());
            $serviceMedico = $this->getServiceLocator()->get('Medico');
            $medicos = $serviceMedico->getMedicosByDiretor(array('usuario_diretor' => $usuario->id));
            //se vir idMedico, pesquisar e popular forma
            $idMedico = $this->params()->fromRoute('idMedico');
            if($idMedico){
                $medico = $serviceMedico->getRecord($idMedico);
                $formMedico->setData($medico);
            }
        }

        //verificar se usuário é do tipo administrador callcenter, se for abrir form para vincular operador
        $operadores = false;
        $formOperador = false;
        if($usuario->id_usuario_tipo == 10){
            $formOperador = new operadorForm('formOperador', $this->getServiceLocator());
            $serviceOperador = $this->getServiceLocator()->get('Operador');
            $operadores = $serviceOperador->getOperadoresByParams(array('usuario_diretor' => $usuario->id));
        }else{
            //pode ser um operador de avaliação diária
            if($usuario->id_usuario_tipo == 12){
                $formOperador = new operadorDiariaForm('formOperador', $this->getServiceLocator());
                $serviceOperador = $this->getServiceLocator()->get('OperadorAvaliacaoDiaria');
                $operadores = $serviceOperador->getOperadoresByParams(array('usuario_diretor' => $usuario->id));
            }   
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            if(isset($dados['abas'])){
                //vincular avaliação
                $result = $serviceAbasUsuario->insert(array('aba' => $dados['abas'], 'empresa' => $dados['empresa'], 'usuario' => $usuario->id));
                if($result){
                    //sucesso criar mensagem e redir para edit
                    $serviceUsuario->update(array('empresa' => $dados['empresa']), array('id' => $usuario->id));
                    $this->flashMessenger()->addSuccessMessage('Avaliação vinculada com sucesso!');                
                    return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao vincular avaliação!'); 
                }
            }else{
                if(isset($dados['auxiliar_empresa'])){
                    $formAuxiliarEmpresa->setData($dados);
                    if($formAuxiliarEmpresa->isValid()){
                        //verificar se deve inserir todas as empresas
                        if($dados['auxiliar_empresa'] == 'todas'){
                            $res = $serviceAuxiliarEmpresas->insert(
                                                                    array('usuario' => $usuario->id, 
                                                                          'empresa' => $dados['auxiliar_empresa']), 
                                                                    $empresasAtivas
                                                            );
                        }else{
                            $res = $serviceAuxiliarEmpresas->insert(array('usuario' => $usuario->id, 'empresa' => $dados['auxiliar_empresa']));
                        }

                        if($res){
                            $this->flashMessenger()->addSuccessMessage('Empresa vinculada com sucesso!');
                        }else{
                            $this->flashMessenger()->addErrorMessage('Não foi possível vincular as empresas!');
                        }
                        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                    }
                }else{
                    if(isset($dados['nome_medico'])){
                        $formMedico->setData($dados);
                        if($formMedico->isValid()){
                            if(isset($medico) && !empty(($medico))){
                                $serviceMedico->update($formMedico->getData(), array('id' => $medico['id']));
                                $this->flashMessenger()->addSuccessMessage('Médico alterado com sucesso!');
                                return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                            }else{
                                $dados['usuario_diretor'] = $usuario->id;

                                //verificar se senha está igual
                                if(empty($dados['senha'])){
                                    $this->flashMessenger()->addWarningMessage('Senha é um campo obrigatório!');
                                    return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                                }
                                if($dados['senha'] != $dados['confirma_senha']){
                                    $this->flashMessenger()->addWarningMessage('Confirmação de senha do médico não confere!');
                                    return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));    
                                }
                                $bcrypt = new Bcrypt();
                                $dados['senha'] = $bcrypt->create($dados['senha']);
                                $serviceMedico->insert($dados);
                                $this->flashMessenger()->addSuccessMessage('Médico inserido com sucesso!');
                                return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                                
                            }
                        }
                    }else{
                        if(isset($dados['nome_operador'])){
                            $formOperador->setData($dados);
                            if($formOperador->isValid()){
                                $dados['usuario_diretor'] = $usuario->id;

                                //verificar se senha está igual
                                if($dados['senha'] != $dados['confirma_senha']){
                                    $this->flashMessenger()->addWarningMessage('Confirmação de senha do operador não confere!');
                                    return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));    
                                }
                                $bcrypt = new Bcrypt();
                                $dados['senha'] = $bcrypt->create($dados['senha']);
                                $serviceOperador->insert($dados);
                                $this->flashMessenger()->addSuccessMessage('Operador inserido com sucesso!');
                                return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                            }
                        }else{
                            $formUsuario->setData($dados);
                            if($formUsuario->isValid()){
                                if((empty($dados['senha']))){
                                    unset($dados['senha']);
                                }else{
                                    $bcrypt = new Bcrypt();
                                    $dados['senha'] = $bcrypt->create($dados['senha']);
                                }
                                if($dados['id_usuario_tipo'] != 5){
                                    $dados['empresa'] = NULL;
                                }
                                $serviceUsuario->update($dados, array('id'  =>  $usuario->id));

                                //se for um diretor médico e status for inativo desativar todos os médicos deste diretor
                                if($usuario->id_usuario_tipo == 8){
                                    if($dados['ativo'] == 'N'){
                                        $this->getServiceLocator()->get('Medico')->update(array('ativo' => 'N'), array('usuario_diretor' => $usuario->id));
                                    }else{
                                        //$this->getServiceLocator()->get('Medico')->update(array('ativo' => 'S'), array('usuario_diretor' => $usuario->id));
                                    }
                                }
                                $this->flashMessenger()->addSuccessMessage('Usuario alterado com sucesso!'); 
                                return $this->redirecionar($usuario->id);
                            }
                        }   
                    }
                }
            }
        }

        //Pesquisar abas para este usuário
        $abas = $serviceAbasUsuario->getAbasByUsuario($usuario->id);

        $abas = $abas->toArray();
        //instanciar form para vincular aba
        $formAba = new abaForm('frmAba', $this->getServiceLocator(), $abas);

        return new ViewModel(array(
                                'formUsuario'       => $formUsuario,
                                'usuario'           => $usuario,
                                'abas'              => $abas,
                                'formAba'           => $formAba,
                                'formEmpresa'      => $formAuxiliarEmpresa,
                                'auxiliarEmpresas'  => $auxiliarEmpresas,
                                'empresasAtivas'    => $empresasAtivas,
                                'formMedico'       => $formMedico,
                                'medicos'           => $medicos,
                                'formOperador'      => $formOperador,
                                'operadores'          => $operadores
                                )
                            );
    }

    public function medicodesativarAction(){
        $this->getServiceLocator()->get('Medico')->update(array('ativo' => 'N'), array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Médico desativado com sucesso!');
        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $this->params()->fromRoute('diretor')));
        return new ViewModel();
    }

    public function medicoativarAction(){
        $this->getServiceLocator()->get('Medico')->ativar($this->params()->fromRoute('id'));
        $this->flashMessenger()->addSuccessMessage('Médico ativado com sucesso!');
        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $this->params()->fromRoute('diretor')));
        return new ViewModel();
    }

    public function operadordesativarAction(){
        $this->getServiceLocator()->get('Operador')->update(array('ativo' => 'N'), array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Operador desativado com sucesso!');
        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $this->params()->fromRoute('diretor')));
        return new ViewModel();
    }

    public function deletarempresaauxiliarAction(){
        $idEmpresaAuxiliar = $this->params()->fromRoute('id');

        $serviceAuxiliarEmpresas = $this->getServiceLocator()->get('AuxiliarEmpresas');

        if($serviceAuxiliarEmpresas->delete(array('id' => $idEmpresaAuxiliar))){
            $this->flashMessenger()->addSuccessMessage('Empresa desvinculada do auxiliar!');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao desvincular empresa do auxiliar!');
        }

        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $this->params()->fromRoute('usuario')));
        return new ViewModel();
    }

    public function deletarempresasauxiliarAction(){
        $serviceAuxiliarEmpresas = $this->getServiceLocator()->get('AuxiliarEmpresas');
        if($serviceAuxiliarEmpresas->delete(array('usuario' => $this->params()->fromRoute('usuario')))){
            $this->flashMessenger()->addSuccessMessage('Empresa desvinculada do auxiliar!');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao desvincular empresa do auxiliar!');
        }

        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $this->params()->fromRoute('usuario')));
        return new ViewModel();
    }

    public function deletarusuarioAction(){
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');

        $res = $serviceUsuario->update(array('ativo' => 'N'), array('id' => $this->params()->fromRoute('id')));
        if($res){
           $this->flashMessenger()->addSuccessMessage('Usuário desativado com sucesso!');  
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao desativar usuário!');
        }
        return $this->redirect()->toRoute('usuario');
    }

    public function desvincularabaAction(){
        $serviceAbas = $this->getServiceLocator()->get('AbasUsuario');

        $res = $serviceAbas->delete(array('id' => $this->params()->fromRoute('aba')));
        if($res){
           $this->flashMessenger()->addSuccessMessage('Avaliação desvinculada com sucesso!');  
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir avaliação!');
        }
        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $this->params()->fromRoute('id')));
    }

    public function mudarempresaAction(){
        $this->layout('layout/avaliacaoindex');

        //criar form com empresas habilitadas para o user
        $session = $this->getServiceLocator()->get('session');
        $usuario = $session->read();
        $formEmpresa = new mudaEmpresaForm('frmEmpresa', $this->getServiceLocator(), $usuario['id']);

        //verificar se veio um post, caso veio..
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            
            //validar form
            $formEmpresa->setData($dados);
            if($formEmpresa->isValid()){  
                //alterar empresa no bd
                $serviceUsuario = $this->getServiceLocator()->get('Usuario');
                if($serviceUsuario->update(array('empresa' => $dados->empresa), array('id' => $usuario['id']))){
                    //alterar a empresa na sessão 
                    $usuario['empresa'] = $dados->empresa;
                    $session->write($usuario);
                    //redir para avaliações
                    $this->flashMessenger()->addSuccessMessage('Empresa alterada com sucesso!'); 
                }else{
                    //erro
                    $this->flashMessenger()->addErrorMessage('Erro ao alterar empresa!');
                }
                return $this->redirect()->toRoute('avaliacoesEmpresa');
            }
        }


        return new ViewModel(array(
                    'formEmpresa' => $formEmpresa
            ));
    }

    private function redirecionar($usuario){
        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario));
    }


}

