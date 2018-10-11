<?php

namespace Avaliacao\Controller;


use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Avaliacao\Form\PesquisaAvaliacao as pesquisaForm;

use Avaliacao\Form\Agendamento as agendamentoForm;
use Avaliacao\Form\Comercial as comercialForm;
use Avaliacao\Form\Processo as processoForm;
use Avaliacao\Form\Qualidade as qualidadeForm;
use Avaliacao\Form\Seguranca as segurancaForm;
use Avaliacao\Form\Ata as ataForm;

use Avaliacao\Controller\DefaultController;

use Zend\Session\Container;

use Zend\EventManager\EventManagerInterface;
use Zend\File\Transfer\Adapter\Http as fileAdapter;


class AvaliacaoController extends DefaultController
{

    public function indexAction()
    {
        $this->layout('layout/avaliacaoindex');
        $sessao = new Container();
        $pesquisaForm = new pesquisaForm('formPesquisa');

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                unset($sessao->parametros);
                $dados = false;
            }else{
                $sessao->parametros = $dados;
            }
        }

        if(isset($sessao->parametros)) {
            $dados = $sessao->parametros;
            $pesquisaForm->setData($dados);
        }

        //Pesquisa avaliações da empresa
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        //pesquisar empresas do usuário
        $serviceAbasUsuario = $this->getServiceLocator()->get('AbasUsuario');
        $parametros = array();
        $parametros['empresas'] = $serviceAbasUsuario->getAbasByUsuario($usuario['id'])->toArray();
    
        if(isset($dados->mes) && !empty($dados->mes)){
            $parametros['mes'] = $dados->mes;
        }

        if(isset($dados->ano) && !empty($dados->ano)){
            $parametros['ano'] = $dados->ano;
        }
        $serviceAgendamento = $this->getServiceLocator()->get('Agendamento');
        $avaliacoes = $serviceAgendamento->getAvaliacoesRespondidasByEmpresa($parametros);
        
        //Cria paginacao
        $Paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        //Verificar se deve exibir o botão de avaliações
        $servicePilha = $this->getServiceLocator()->get('PilhaAvaliacoes');
        $linkAvaliacoes = $servicePilha->getAvaliacoesByDate(date('Y-m-d'), $parametros, 'C')->toArray();

        return new ViewModel(array(
                                'formPesquisa'      => $pesquisaForm,
                                'avaliacoes'          => $Paginator,
                                'linkAvaliacoes'        => $linkAvaliacoes
                            ));
    }

    public function listaavaliacoesabertoAction(){
        $this->layout('layout/avaliacaoindex');
        $servicePilha = $this->getServiceLocator()->get('PilhaAvaliacoes');

        $usuario = $this->getServiceLocator()->get('session')->read();
        $avaliacoes = $servicePilha->getAvaliacoesByDate(date('Y-m-d'), $usuario['empresa'], 'C');
        
        return new ViewModel(array('avaliacoes' => $avaliacoes));
    }

    public function agendamentoAction()
    { 
        //Pesquisar campos
        $usuario = $this->getServiceLocator()->get('session')->read();
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(1, $usuario['empresa'])->toArray();

        $formAgendamento = new agendamentoForm('formAgendamento', $this->getServiceLocator(), $campos);
        $serviceAgendamento = $this->getServiceLocator()->get('Agendamento');
        $view = parent::avaliacao($formAgendamento, $serviceAgendamento, 1, 'Agendamento', 'avaliacaoComercial', $campos);
        return $view;
    }

    public function comercialAction()
    { 
        //Pesquisar campos
        $usuario = $this->getServiceLocator()->get('session')->read();
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(9, $usuario['empresa'])->toArray();

        $formComercial = new comercialForm('formComercial', $this->getServiceLocator(), $campos);
        $serviceComercial = $this->getServiceLocator()->get('Comercial');
        $view = parent::avaliacao($formComercial, $serviceComercial, 9, 'Comercial', 'avaliacaoProcesso', $campos);
        return $view;
    }

    public function processoAction()
    { 
        //Pesquisar campos
        $usuario = $this->getServiceLocator()->get('session')->read();
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(2, $usuario['empresa'])->toArray();

        $formProcesso = new processoForm('formProcesso', $this->getServiceLocator(), $campos);
        $serviceProcesso = $this->getServiceLocator()->get('Processo');
        $view = parent::avaliacao($formProcesso, $serviceProcesso, 2, 'Processos e Produção', 'avaliacaoQualidade', $campos);
        return $view;
    }

    public function qualidadeAction()
    { 
        //Pesquisar campos
        $usuario = $this->getServiceLocator()->get('session')->read();
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(3, $usuario['empresa'])->toArray();

        $formQualidade = new qualidadeForm('formQualidade', $this->getServiceLocator(), $campos);
        $serviceQualidade = $this->getServiceLocator()->get('Qualidade');
        $view = parent::avaliacao($formQualidade, $serviceQualidade, 3, 'Qualidade e Segurança', 'avaliacaoSeguranca', $campos);
        return $view;  
    }

    public function segurancaAction()
    { 
        //Pesquisar campos
        $usuario = $this->getServiceLocator()->get('session')->read();
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(4, $usuario['empresa'])->toArray();

        $formSeguranca = new segurancaForm('formSeguranca', $this->getServiceLocator(), $campos);
        $serviceSeguranca = $this->getServiceLocator()->get('Seguranca');
        $view = parent::avaliacao($formSeguranca, $serviceSeguranca, 4, 'Experiência e satisfação do paciente', 'avaliacaoAta', $campos);
        return $view;
    }

    public function ataAction(){
        $formAta = new ataForm('formAta', $this->getServiceLocator());
        $serviceAta = $this->getServiceLocator()->get('Ata');
        $view = parent::avaliacao($formAta, $serviceAta, 5, 'ata', 'avaliacoesEmpresa');
        return $view;
    }

    public function arquivosAction(){
        $this->layout('layout/avaliacaocliente');
        $empresa = $this->params()->fromRoute('empresa');
        $ano = $this->params()->fromRoute('ano');
        $mes = $this->params()->fromRoute('mes');
        $avaliacao = $this->params()->fromRoute('avaliacao');
        $redir = $this->params()->fromRoute('redirecionar');
        $menu = $this->params()->fromRoute('menu');

        if(!$empresa){
            $usuario = $this->getServiceLocator()->get('session')->read();
            $empresa = $usuario['empresa'];
        }   
       
        $arquivos = $this->getServiceLocator()->get('EmpresaArquivo')->getRecords(1, 'empresa', array('*'), 'nome');
        return new ViewModel(array('arquivos' => $arquivos, 'redir' => $redir, 'ano' => $ano, 'mes' => $mes, 'avaliacao' => $avaliacao, 'menu' => $menu));
    }

    public function downloadAction(){
        $idAvaliacao = $this->params()->fromRoute('id');
        $campo = $this->params()->fromRoute('campo');
        $aba = $this->params()->fromRoute('aba');

        $serviceAba = $this->defineService($aba);
        
        $avaliacao = $serviceAba->getRecord($idAvaliacao);

        $fileName = $avaliacao[$campo];
        
        if(!is_file($fileName)) {
            //Não foi possivel encontrar o arquivo
        }
        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'whatever your content type is')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

    public function downloadplanilhaAction(){
        $sessao = new Container();
        $fileName = $sessao->offsetGet('xlsx');

        
        if(!is_file($fileName)) {
            //Não foi possivel encontrar o arquivo
        }
        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'whatever your content type is')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

    public function visualizaragendamentoAction(){
        $this->layout('layout/avaliacaocliente');

        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(1, $usuario['empresa'])->toArray();
        $formAvaliacao = new agendamentoForm('frmAgendamento', $this->getServiceLocator(), $campos);
        $serviceAgendamento = $this->getServiceLocator()->get('Agendamento');
        
        
        //pesquisar avaliação
        $avaliacao = $serviceAgendamento->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $usuario['empresa'], 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
            $formAuditado = new agendamentoForm('frmAuditado', $this->getServiceLocator(), $campos);
            if($avaliacao->auditado == 'S'){
                //pesquisar avaliacao auditada
                $avaliacaoAuditada = $serviceAgendamento->getRecord($avaliacao->id, 'id_formulario');
                $formAuditado->setData($avaliacaoAuditada);
            }else{
                $avaliacaoAuditada = false;
                $formAuditado = $formAvaliacao;
            }

            foreach ($formAuditado as $field) {
                $formAuditado->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
                $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
            }
        }else{
            $avaliacaoAuditada = false;
            $formAuditado = $formAvaliacao;
        }


        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'ano' => $this->params()->fromRoute('ano'), 
                                    'mes' => $this->params()->fromRoute('mes'),
                                    'campos'    => $campos,
                                    'empresa'   => $usuario['empresa']
                            ));
    }

    public function visualizarcomercialAction(){
        $this->layout('layout/avaliacaocliente');

        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(9, $usuario['empresa'])->toArray();
        $formAvaliacao = new comercialForm('frmComercial', $this->getServiceLocator(), $campos);
        $serviceComercial = $this->getServiceLocator()->get('Comercial');
        
        
        //pesquisar avaliação
        $avaliacao = $serviceComercial->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $usuario['empresa'], 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
            $formAuditado = new comercialForm('frmAuditado', $this->getServiceLocator(), $campos);
            if($avaliacao->auditado == 'S'){
                //pesquisar avaliacao auditada
                $avaliacaoAuditada = $serviceComercial->getRecord($avaliacao->id, 'id_formulario');
                $formAuditado->setData($avaliacaoAuditada);
            }else{
                $avaliacaoAuditada = false;
                $formAuditado = $formAvaliacao;
            }

            foreach ($formAuditado as $field) {
                $formAuditado->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
                $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
            }
        }else{
            $avaliacaoAuditada = false;
            $formAuditado = $formAvaliacao;
        }


        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'ano' => $this->params()->fromRoute('ano'), 
                                    'mes' => $this->params()->fromRoute('mes'),
                                    'campos'    => $campos,
                                    'empresa'   => $usuario['empresa']
                            ));
    }

    public function visualizarprocessoAction(){
        $this->layout('layout/avaliacaocliente');
        $usuario = $this->getServiceLocator()->get('session')->read();

        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(2, $usuario['empresa'])->toArray();
        
        $formAvaliacao = new processoForm('frmProcesso', $this->getServiceLocator(), $campos);
        $serviceProcesso = $this->getServiceLocator()->get('Processo');
        
        
        //pesquisar avaliação
        $avaliacao = $serviceProcesso->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $usuario['empresa'], 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
            
            $formAuditado = new processoForm('frmAuditado', $this->getServiceLocator(), $campos);
            if($avaliacao->auditado == 'S'){
                //pesquisar avaliacao auditada
                $avaliacaoAuditada = $serviceProcesso->getRecord($avaliacao->id, 'id_formulario');
                $formAuditado->setData($avaliacaoAuditada);
            }else{
                $avaliacaoAuditada = false;
                $formAuditado = $formAvaliacao;
            }

            foreach ($formAuditado as $field) {
                $formAuditado->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
                $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
            }
        }else{
            $avaliacaoAuditada = false;
            $formAuditado = $formAvaliacao;
        }

        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'ano' => $this->params()->fromRoute('ano'), 
                                    'mes' => $this->params()->fromRoute('mes'),
                                    'campos'    => $campos,
                                    'empresa'   => $usuario['empresa']
                            ));
    }

    public function visualizarqualidadeAction(){
        $this->layout('layout/avaliacaocliente');
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(3, $usuario['empresa'])->toArray();
        $formAvaliacao = new qualidadeForm('frmQualidade', $this->getServiceLocator(), $campos);
        $serviceQualidade = $this->getServiceLocator()->get('Qualidade');
        
        
        //pesquisar avaliação
        $avaliacao = $serviceQualidade->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $usuario['empresa'], 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
            
            $formAuditado = new qualidadeForm('frmAuditado', $this->getServiceLocator(), $campos);
            if($avaliacao->auditado == 'S'){
                //pesquisar avaliacao auditada
                $avaliacaoAuditada = $serviceQualidade->getRecord($avaliacao->id, 'id_formulario');
                $formAuditado->setData($avaliacaoAuditada);
            }else{
                $avaliacaoAuditada = false;
                $formAuditado = $formAvaliacao;
            }

            foreach ($formAuditado as $field) {
                $formAuditado->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
                $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
            }
        }else{
            $avaliacaoAuditada = false;
            $formAuditado = $formAvaliacao;
        }
        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'ano' => $this->params()->fromRoute('ano'), 
                                    'mes' => $this->params()->fromRoute('mes'),
                                    'campos'    => $campos,
                                    'empresa'   => $usuario['empresa']
                            ));
    }

    public function visualizarsegurancaAction(){
        $this->layout('layout/avaliacaocliente');
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(4, $usuario['empresa'])->toArray();
        $formAvaliacao = new segurancaForm('frmSeguranca', $this->getServiceLocator(), $campos);
        $serviceSeguranca = $this->getServiceLocator()->get('Seguranca');
        
        
        //pesquisar avaliação
        $avaliacao = $serviceSeguranca->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $usuario['empresa'], 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes'),
                                                    ),
                                                    'id'
                                            )->current();
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
            
            $formAuditado = new segurancaForm('frmAuditado', $this->getServiceLocator(), $campos);
            if($avaliacao->auditado == 'S'){
                //pesquisar avaliacao auditada
                $avaliacaoAuditada = $serviceSeguranca->getRecord($avaliacao->id, 'id_formulario');
                $formAuditado->setData($avaliacaoAuditada);
            }else{
                $avaliacaoAuditada = false;
                $formAuditado = $formAvaliacao;
            }

            foreach ($formAuditado as $field) {
                $formAuditado->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
                $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
            }
        }else{
            $avaliacaoAuditada = false;
            $formAuditado = $formAvaliacao;
        }
        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'ano' => $this->params()->fromRoute('ano'), 
                                    'mes' => $this->params()->fromRoute('mes'),
                                    'campos'    => $campos,
                                    'empresa'   => $usuario['empresa']
                            ));
    }

    public function visualizarataAction(){
        $this->layout('layout/avaliacaocliente');
        $formAvaliacao = new ataForm('frmAta', $this->getServiceLocator());
        $serviceAta = $this->getServiceLocator()->get('Ata');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        //pesquisar avaliação
        $avaliacao = $serviceAta->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $usuario['empresa'], 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
            foreach ($formAvaliacao as $field) {
                $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
            }
        }
        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'avaliacao'         => $avaliacao,
                                    'ano' => $this->params()->fromRoute('ano'), 
                                    'mes' => $this->params()->fromRoute('mes'),
                                    'empresa'   => $usuario['empresa']
                            ));
    }

}

?>