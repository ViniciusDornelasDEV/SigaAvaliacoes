<?php

namespace Avaliacao\Controller;

use Avaliacao\Controller\DefaultController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Avaliacao\Form\PesquisaAvaliacaoAdm as pesquisaForm;

use Avaliacao\Form\Agendamento as agendamentoForm;
use Avaliacao\Form\Comercial as comercialForm;
use Avaliacao\Form\Processo as processoForm;
use Avaliacao\Form\Qualidade as qualidadeForm;
use Avaliacao\Form\Seguranca as segurancaForm;
use Avaliacao\Form\Ata as ataForm;

use Avaliacao\Form\AvaliacaoQualitativaMedico as avaliacaoQualitativaForm;
use Avaliacao\Form\AvaliacaoPidMedico as avaliacaoPidForm;

use Zend\Session\Container;

use Zend\EventManager\EventManagerInterface;
use Zend\File\Transfer\Adapter\Http as fileAdapter;



use Zend\Crypt\Password\Bcrypt;

class AvaliacaoadmController extends DefaultController
{
    private $numeroLinhas;
    public function indexAction()
    {
        //Verificar o tipo de avaliação
        $tipoAvaliacao = $this->params()->fromRoute('tipo');
        
        //clínica
        if($tipoAvaliacao == 'C'){
            $serviceAvaliacao = $this->getServiceLocator()->get('Agendamento');
                        
        }else{
            //médico
            if($tipoAvaliacao == 'M'){
                $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoQualitativa');

            }
        }

        $pesquisaForm = new pesquisaForm('formPesquisa', $this->getServiceLocator());

        $sessao = new Container();

        $parametros = array('id_formulario' => null);
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                //redir para mesma página
                unset($sessao->parametros);
                return $this->redirect()->toRoute('listaAvaliacaoAdm', array('tipo' => 'C'));
            }else{
                //paràmetros da pesquisa
                $sessao->parametros = $dados;
            }
        }

        if(isset($sessao->parametros)) {
            $pesquisaForm->setData($sessao->parametros);
            if(!empty(($sessao->parametros->mes))){
                $parametros['mes'] = $sessao->parametros->mes;
            }
            if(!empty($sessao->parametros->ano)){
                $parametros['ano'] = $sessao->parametros->ano;
            }

            if(!empty($sessao->parametros->empresa)){
                $parametros['tb_medico_avaliacao_qualitativa.empresa'] = $sessao->parametros->empresa;
            }
        }
        
        //$pesquisaForm->setData($sessao->parametros);
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 7) {
            $parametros['ae.usuario'] = $usuario['id'];
            $avaliacoes = $serviceAvaliacao->getAvaliacoesRespondidasAuxiliarByEmpresas($parametros);
        }else{
            $avaliacoes = $serviceAvaliacao->getAvaliacoesRespondidasByEmpresas($parametros);
        }

        //Cria paginacao
        $Paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        return new ViewModel(array(
                                'formPesquisa'      => $pesquisaForm,
                                'avaliacoes'          => $Paginator,
                                'tipoAvaliacao'     => $tipoAvaliacao
                            ));
    }
  

    public function visualizaragendamentoAction(){
        $this->layout('layout/avaliacaocliente');
        $serviceAgendamento = $this->getServiceLocator()->get('Agendamento');
        
        //pesquisar avaliação
        $avaliacao = $serviceAgendamento->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $this->params()->fromRoute('empresa'), 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();

        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(1, $this->params()->fromRoute('empresa'))->toArray();
        $formAvaliacao = new agendamentoForm('frmAgendamento', $this->getServiceLocator(), $campos);
        if($avaliacao){
            //pesquisar campos
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
            $formAuditado = false;
            $avaliacaoAuditada = false;
        }

        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'empresa'           => $this->params()->fromRoute('empresa'),
                                    'ano'               => $this->params()->fromRoute('ano'),
                                    'mes'               => $this->params()->fromRoute('mes'),
                                    'campos'            => $campos
                            ));
    }

    public function visualizarcomercialAction(){
        $this->layout('layout/avaliacaocliente');
        $serviceComercial = $this->getServiceLocator()->get('Comercial');
        
        //pesquisar avaliação
        $avaliacao = $serviceComercial->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $this->params()->fromRoute('empresa'), 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();

        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(9, $this->params()->fromRoute('empresa'))->toArray();
        $formAvaliacao = new comercialForm('frmAgendamento', $this->getServiceLocator(), $campos);
        if($avaliacao){
            //pesquisar campos
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
            $formAuditado = false;
            $avaliacaoAuditada = false;
        }

        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'empresa'           => $this->params()->fromRoute('empresa'),
                                    'ano'               => $this->params()->fromRoute('ano'),
                                    'mes'               => $this->params()->fromRoute('mes'),
                                    'campos'            => $campos
                            ));
    }

    public function visualizarprocessoAction(){
        $this->layout('layout/avaliacaocliente');
        $serviceProcesso = $this->getServiceLocator()->get('Processo');
        
        //pesquisar avaliação
        $avaliacao = $serviceProcesso->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $this->params()->fromRoute('empresa'), 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();

        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(2, $this->params()->fromRoute('empresa'))->toArray();
        $formAvaliacao = new processoForm('frmProcesso', $this->getServiceLocator(), $campos);
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
            $formAuditado = false;
            $avaliacaoAuditada = false;
        }
        
        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'empresa'           => $this->params()->fromRoute('empresa'),
                                    'ano'               => $this->params()->fromRoute('ano'),
                                    'mes'               => $this->params()->fromRoute('mes'),
                                    'campos'            => $campos
                            ));
    }

    public function visualizarqualidadeAction(){
        $this->layout('layout/avaliacaocliente');
        $serviceQualidade = $this->getServiceLocator()->get('Qualidade');
        
        
        //pesquisar avaliação
        $avaliacao = $serviceQualidade->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $this->params()->fromRoute('empresa'), 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(3, $this->params()->fromRoute('empresa'))->toArray();
        $formAvaliacao = new qualidadeForm('frmQualidade', $this->getServiceLocator(), $campos);
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
            $formAuditado = false;
            $avaliacaoAuditada = false;
        }
        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'empresa'           => $this->params()->fromRoute('empresa'),
                                    'ano'               => $this->params()->fromRoute('ano'),
                                    'mes'               => $this->params()->fromRoute('mes'),
                                    'campos'            => $campos
                            ));
    }

    public function visualizarsegurancaAction(){
        $this->layout('layout/avaliacaocliente');
        $serviceSeguranca = $this->getServiceLocator()->get('Seguranca');
        
        
        //pesquisar avaliação
        $avaliacao = $serviceSeguranca->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $this->params()->fromRoute('empresa'), 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    ),
                                                    'id'
                                            )->current();

        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(4, $this->params()->fromRoute('empresa'))->toArray();
        $formAvaliacao = new segurancaForm('frmSeguranca', $this->getServiceLocator(), $campos);

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
            $formAuditado = false;
            $avaliacaoAuditada = false;
        }
        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'formAuditado'      =>  $formAuditado,
                                    'avaliacaoAuditada' => $avaliacaoAuditada,
                                    'avaliacao'         => $avaliacao,
                                    'empresa'           => $this->params()->fromRoute('empresa'),
                                    'ano'               => $this->params()->fromRoute('ano'),
                                    'mes'               => $this->params()->fromRoute('mes'),
                                    'campos'            => $campos
                            ));
    }

    public function visualizarataAction(){
        $this->layout('layout/avaliacaocliente');
        $formAvaliacao = new ataForm('frmAta');
        $serviceAta = $this->getServiceLocator()->get('Ata');
        
        //pesquisar avaliação
        $avaliacao = $serviceAta->getRecordsFromArray(
                                                    array(
                                                        'empresa' => $this->params()->fromRoute('empresa'), 
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
                                    'empresa'           => $this->params()->fromRoute('empresa'),
                                    'ano'               => $this->params()->fromRoute('ano'),
                                    'mes'               => $this->params()->fromRoute('mes')
                            ));
    }

    public function visualizararquivosAction(){
        $this->layout('layout/avaliacaocliente');
        
        $empresa = $this->params()->fromRoute('empresa');

        $arquivos = $this->getServiceLocator()->get('EmpresaArquivo')->getRecords($empresa, 'empresa');
        return new ViewModel(array(
                                'arquivos' => $arquivos,
                                'empresa'           => $empresa,
                                'ano'               => $this->params()->fromRoute('ano'),
                                'mes'               => $this->params()->fromRoute('mes')
                            ));
    }

    public function visualizarqualitativaAction(){
        $this->layout('layout/medico');
        
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoQualitativa');

        //se for do tipo médico, pesquisar também por usuário
        
        $avaliacao = $serviceAvaliacao->getRecord($this->params()->fromRoute('id'));
        $medico = $this->getServiceLocator()->get('Medico')->getRecord($avaliacao->medico);

        //se for um usuário do tipo MÉDICO - AVALIADO
        $tipoUsuario = 'C';
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 9){
            $tipoUsuario = 'M';
            if($usuario['medico'] != $medico->id){
                $this->flashMessenger()->addWarningMessage('Médico não encontrado!');
                return $this->redirect()->toRoute('listarAvaliacoesMedico');
            }
        }

        if($usuario['id_usuario_tipo'] == 8){
            $tipoUsuario = 'M';
            if($usuario['id'] != $medico['usuario_diretor'] && $usuario['id'] != $medico['usuario_diretor2']){
                $this->flashMessenger()->addWarningMessage('Médico não encontrado!');
                return $this->redirect()->toRoute('indexFinalizadas');
            }
        }

        //pesquisar campos da avaliação para a empresa
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(6, $medico->empresa)->toArray();
        $formAvaliacao = new avaliacaoQualitativaForm('formAvaliacao', $this->getServiceLocator(), $campos);
        
        if($avaliacao){
            //pesquisar campos
            $formAvaliacao->setData($avaliacao);
            
            $formAuditado = new avaliacaoQualitativaForm('frmAuditado', $this->getServiceLocator(), $campos);
            if($avaliacao->auditado == 'S'){
                //pesquisar avaliacao auditada
                $avaliacaoAuditada = $serviceAvaliacao->getRecord($avaliacao->id, 'id_formulario');
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
            $formAuditado = false;
            $avaliacaoAuditada = false;
        }
        
        $periodoAvaliacao = $this->getServiceLocator()->get('PilhaAvaliacoes')->getRecordFromArray(array(
                    'ano_referencia' => $avaliacao->ano,
                    'mes_referencia' => $avaliacao->mes,
                    'aba'            => $tipoUsuario
                ));
        return new ViewModel(array(
                'formAvaliacao'     => $formAvaliacao,
                'formAuditado'      => $formAuditado,
                'avaliacao'         => $avaliacao,
                'avaliacaoAuditada' => $avaliacaoAuditada,
                'medico'            => $medico,
                'periodo'           => $periodoAvaliacao,
                'campos'            => $campos,
                'usuario'           => $usuario
            ));
    }

    public function visualizarpidAction(){
        $this->layout('layout/medico');
        $usuario = $this->getServiceLocator()->get('session')->read();

        $idMedico = $this->params()->fromRoute('medico');
        $medico = $this->getServiceLocator()->get('Medico')->getRecord($idMedico);

        //se for um usuário do tipo MÉDICO - AVALIADO
        if($usuario['id_usuario_tipo'] == 9){
            if($usuario['medico'] != $idMedico){
                $this->flashMessenger()->addWarningMessage('Médico não encontrado!');
                return $this->redirect()->toRoute('listarAvaliacoesMedico');
            }
        }
        
        if($usuario['id_usuario_tipo'] == 8){
            $tipoUsuario = 'M';
            if($usuario['id'] != $medico['usuario_diretor'] && $usuario['id'] != $medico['usuario_diretor2']){
                $this->flashMessenger()->addWarningMessage('Médico não encontrado!');
                return $this->redirect()->toRoute('indexFinalizadas');
            }
        }

        //pesquisar campos da avaliação para a empresa
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(7, $medico->empresa)->toArray();
        $formAvaliacao = new avaliacaoPidForm('formAvaliacao', $campos);

        //Desabilitar campos
        foreach ($formAvaliacao as $field) {
            $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
        }
        
        //pesquisar período de avaliação
        $periodoAvaliacao = $this->getServiceLocator()->get('PilhaAvaliacoes')->getRecord($this->params()->fromRoute('periodo'));

        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoPid');
        //pesquisar avaliacao
        $avaliacao = $serviceAvaliacao->getRecordFromArray(array(
                        'ano'       => $periodoAvaliacao['ano_referencia'],
                        'mes'       => $periodoAvaliacao['mes_referencia'],
                        'medico'    => $medico->id
                    ));

        if(!$avaliacao){
            $this->flashMessenger()->addWarningMessage('Avaliação não encontrada!');

            //se for médico redirecionar para a listagem de avaliações do médico
            if($usuario['id_usuario_tipo'] == 9){
                return $this->redirect()->toRoute('listarAvaliacoesMedico');
            }

            if($usuario['id_usuario_tipo'] == 8){
                return $this->redirect()->toRoute('indexFinalizadas');
            }

            return $this->redirect()->toRoute('listaAvaliacaoAdm', array('tipo' => 'M'));
        }

        if($avaliacao){
            //pesquisar campos
            $formAvaliacao->setData($avaliacao);
            
            $formAuditado = new avaliacaoPidForm('frmPid', $campos);
            if($avaliacao->auditado == 'S'){
                //pesquisar avaliacao auditada
                $avaliacaoAuditada = $serviceAvaliacao->getRecord($avaliacao->id, 'id_formulario');
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
            $formAuditado = false;
            $avaliacaoAuditada = false;
        }


        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($medico->empresa);
        return new ViewModel(array(
            'formAvaliacao'     => $formAvaliacao,
            'formAuditado'      => $formAuditado,
            'empresa'           => $empresa,
            'periodo'           => $periodoAvaliacao,
            'medico'            => $medico,
            'idQualitativa'     => $this->params()->fromRoute('qualitativa'),
            'avaliacao'         => $avaliacao,
            'avaliacaoAuditada' => $avaliacaoAuditada,
            'campos'            => $campos,
            'usuario'           => $usuario
        ));
    }

    public function excluiravaliacaoAction(){
        $aba = $this->params()->fromRoute('aba');
        $idAvaliacao = $this->params()->fromRoute('id');
        $service = parent::defineService($aba);

        //excluir audição
        if($aba < 5){
            $service->delete(array('id_formulario' => $idAvaliacao));
        }

        //excluir avaliação
        $service->delete(array('id' => $idAvaliacao));
        //gerar mensagem
        $this->flashMessenger()->addSuccessMessage('Avaliação excluída com sucesso!');

        //redirecionar para lista de avaliações
        $this->redirect()->toRoute('listaAvaliacaoAdm', array('tipo' => 'C'));
        
        return new ViewModel();
    }

    public function exportaragendamentoAction(){
        $avaliacao = $this->getServiceLocator()->get('Agendamento')->getRecord($this->params()->fromRoute('id'));

        $viewModel = new ViewModel();
        $viewModel->setVariables(array('avaliacao' => $avaliacao))
              ->setTerminal(true);

        return $viewModel;
    }

    public function exportarprocessoAction(){
        $avaliacao = $this->getServiceLocator()->get('Processo')->getRecord($this->params()->fromRoute('id'));

        $viewModel = new ViewModel();
        $viewModel->setVariables(array('avaliacao' => $avaliacao))
              ->setTerminal(true);

        return $viewModel;
    }

    public function exportarqualidadeAction(){
        $avaliacao = $this->getServiceLocator()->get('Qualidade')->getRecord($this->params()->fromRoute('id'));

        $viewModel = new ViewModel();
        $viewModel->setVariables(array('avaliacao' => $avaliacao))
              ->setTerminal(true);

        return $viewModel;
    }

    public function exportarsegurancaAction(){
        $avaliacao = $this->getServiceLocator()->get('Seguranca')->getRecord($this->params()->fromRoute('id'));

        $viewModel = new ViewModel();
        $viewModel->setVariables(array('avaliacao' => $avaliacao))
              ->setTerminal(true);

        return $viewModel;
    }

    public function planilhaclienteAction(){
        //Pesquisar avaliações, caso tenha alguma não respondida retornar erro
        $mesReferencia = (int) $this->params()->fromRoute('mes');
        $anoReferencia = $this->params()->fromRoute('ano');
        $serviceEmpresa = $this->getServiceLocator()->get('Empresa');


        //Início do excel
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel = $this->gerarCabecalho($objPHPExcel);

        //PESQUISAR DADOS E ADICIONAR NA TABELA
        $this->numeroLinhas = 1;
        $params = array(
                        'ano'       => $anoReferencia, 
                        'mes'       => $mesReferencia,
                        'auditado'  => 'I'
                    );
        $serviceCampo = $this->getServiceLocator()->get('Campo');

        $empresas = $serviceEmpresa->fetchAll()->toArray();

        $campos = array();
        $campos['comercial']  = $serviceCampo->getCamposByAba(9)->toArray();
        $campos['agendamento']  = $serviceCampo->getCamposByAba(1)->toArray();
        $campos['processo']  = $serviceCampo->getCamposByAba(2)->toArray();
        $campos['qualidade']  = $serviceCampo->getCamposByAba(3)->toArray();
        $campos['seguranca']  = $serviceCampo->getCamposByAba(4)->toArray();


        foreach ($empresas as $empresa) {
            $params['empresa'] = $empresa['id'];
            $objPHPExcel = $this->relatorioEmpresa($objPHPExcel, $params, $empresa, $campos);
        }

        foreach ($empresas as $empresa) {
            $params['empresa'] = $empresa['id'];
            //PESQUISAR DADOS DE AGENDAMENTO
            $avaliacaoAgendamento = $this->getServiceLocator()->get('Agendamento')->getavaliacaoByParams($params)->current();
            if($avaliacaoAgendamento){
                //GERAR CAMPOS DE SEGURANÇA
                $objPHPExcel = $this->gerarRelatorio($objPHPExcel, $avaliacaoAgendamento, $campos['agendamento'], $empresa);
            }
        }
                
        // Save Excel 2007 file
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        
        $diretorio = 'public/relatorios';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/'.$mesReferencia.'-'.$anoReferencia.'.xlsx';
        $objWriter->save($fileName);
        
        //salvar caminho do arquivo na sessão
        $sessao = new Container();
        $sessao->offsetSet('xlsx', $fileName);

        //enviar para download
        return $this->redirect()->toRoute('downloadPlanilha');

    }

    //planílha de médicos
    public function planilhamedicoAction(){
        //caso venha o id de um pid tenho que enviar para download de arquivo
        if($this->params()->fromRoute('pid') && !empty($this->params()->fromRoute('pid'))){
            //pesquisar avaliação pid
            $avaliacaoPid = $this->getServiceLocator()->get('AvaliacaoPid')->getRecord($this->params()->fromRoute('pid'));
            $sessao = new Container();
            $sessao->offsetSet('xlsx', $avaliacaoPid->arquivo);

            //enviar para download
            return $this->redirect()->toRoute('downloadPlanilha');
            
        }


        //Início do excel
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel = $this->gerarCabecalho($objPHPExcel);

        //Pesquisar avaliações
        $serviceQualitativa = $this->getServiceLocator()->get('AvaliacaoQualitativa');
        $avaliacaoModelo = $serviceQualitativa->getRecord($this->params()->fromRoute('qualitativa'));
    
        $avaliacoesQualitativa = $serviceQualitativa
                                    ->getAvaliacoesByPeriodo($avaliacaoModelo->ano, $avaliacaoModelo->mes)
                                    ->toArray();

        
        //opções
        $opcoesQualitativa = $this->getServiceLocator()->get('CampoMedicoOpcoes')->fetchAll()->toArray();

        //gerar campos de avaliação qualitativa
        $empresa = 0;
        $numeroLinhas = 0;
        foreach ($avaliacoesQualitativa as $avaliacaoQualitativa) {
            if($avaliacaoQualitativa['empresa'] != $empresa){
                //gerar avaliação default(CAMPOS) para a empresa
                $empresa = $avaliacaoQualitativa['empresa'];
                $camposQualitativa = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(6, $empresa)->toArray();
                $camposPid = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(7, $empresa)->toArray();
            }

            //percorrer todos os campos
            foreach ($camposQualitativa as $campo) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numeroLinhas, $avaliacaoQualitativa['nome_empresa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numeroLinhas, $avaliacaoQualitativa['nome_medico']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numeroLinhas, 'Qualitativa');
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numeroLinhas, $campo['label']);

                //procurar a resposta correta no array
                foreach ($opcoesQualitativa as $opcao) {
                    if($opcao['id'] == $avaliacaoQualitativa[$campo['nome_campo']]){
                        $resposta = $opcao['opcao'];
                    }
                }
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numeroLinhas, $resposta);
                $numeroLinhas++;
            }

            //percorrer campos de pid
            if(empty($avaliacaoQualitativa['arquivo_pid'])){
                foreach ($camposPid as $campo) {
                    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numeroLinhas, $avaliacaoQualitativa['nome_empresa']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numeroLinhas, $avaliacaoQualitativa['nome_medico']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numeroLinhas, 'PID');
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numeroLinhas, $campo['label']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numeroLinhas, $avaliacaoQualitativa[$campo['nome_campo']]);
                    $numeroLinhas++;
                }
            }else{
                $url = 'http://timesistemas.com.br/avaliacao/exportar/avaliacao/medico/'.$this->params()->fromRoute('qualitativa').'/'.$avaliacaoQualitativa['id_pid'];
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numeroLinhas, $avaliacaoQualitativa['nome_empresa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numeroLinhas, $avaliacaoQualitativa['nome_medico']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numeroLinhas, 'PID');
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numeroLinhas, 'Avaliação em anexo');
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numeroLinhas, $url);
                $numeroLinhas++;
            }
        }

        // Save Excel 2007 file
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        
        $diretorio = 'public/relatorios';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/'.$avaliacaoModelo->ano.'-'.$avaliacaoModelo->ano.'-medico.xlsx';
        $objWriter->save($fileName);
        
        //salvar caminho do arquivo na sessão
        $sessao = new Container();
        $sessao->offsetSet('xlsx', $fileName);

        //enviar para download
        return $this->redirect()->toRoute('downloadPlanilha');

    }

    private function relatorioEmpresa($objPHPExcel, $params, $empresa, $campos){
        //PESQUISAR DADOS DE COMERCIAL
        $avaliacaoComercial = $this->getServiceLocator()
                            ->get('Comercial')->getavaliacaoByParams($params)->current();
        if($avaliacaoComercial){
            //GERAR CAMPOS DE COMERCIAL
            $objPHPExcel = $this->gerarRelatorio($objPHPExcel, $avaliacaoComercial, $campos['comercial'], $empresa);
        }

        //PESQUISAR DADOS DE PROCESSO
        $avaliacaoProcesso = $this->getServiceLocator()
                            ->get('Processo')->getavaliacaoByParams($params)->current();
        if($avaliacaoProcesso){
            //GERAR CAMPOS DE PROCESSO
            $objPHPExcel = $this->gerarRelatorio($objPHPExcel, $avaliacaoProcesso, $campos['processo'], $empresa);
        }
     

        //PESQUISAR DADOS DE QUALIDADE
        $avaliacaoQualidade = $this->getServiceLocator()->get('Qualidade')->getavaliacaoByParams($params)->current();
        if($avaliacaoQualidade){
            //GERAR CAMPOS DE QUALIDADE
            $objPHPExcel = $this->gerarRelatorio($objPHPExcel, $avaliacaoQualidade, $campos['qualidade'], $empresa);
        }
     

        //PESQUISAR DADOS DE SEGURANÇA
        $avaliacaoSeguranca = $this->getServiceLocator()->get('Seguranca')->getavaliacaoByParams($params)->current();
        if($avaliacaoSeguranca){
            //GERAR CAMPOS DE SEGURANÇA
            $objPHPExcel = $this->gerarRelatorio($objPHPExcel, $avaliacaoSeguranca, $campos['seguranca'], $empresa);
        }

        return $objPHPExcel;
    }

    private function gerarCabecalho($objPHPExcel){
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Unidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Categoria');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Sub-categoria');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Indicador');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Resultado');
        $this->numeroLinhas++;

        return $objPHPExcel;
    }

    private function gerarRelatorio($objPHPExcel, $avaliacao, $campos, $empresa){
        
        foreach ($campos as $campo) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, $empresa['nome_empresa']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$this->numeroLinhas, $campo['nome_aba']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$this->numeroLinhas, $campo['nome_categoria']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$this->numeroLinhas, $campo['label']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$this->numeroLinhas, $this->defineResultado($avaliacao[$campo['nome_campo']]));
            $this->numeroLinhas++;
        }
        return $objPHPExcel;
    }

    private function defineResultado($resultado){
        switch ($resultado) {
            case 1:
                return 0;
                break;
            case 2:
                return 1;
                break;
            case 3:
                return 2;
                break;
            case 4:
                return 3;
                break;
            case 5:
                return 4;
                break;
            default:
                return 4;
               
        }
    }

}

?>



