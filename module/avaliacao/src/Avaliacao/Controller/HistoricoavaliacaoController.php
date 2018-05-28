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


class HistoricoavaliacaoController extends DefaultController
{

    public function visualizaragendamentoAction(){
        $this->layout('layout/avaliacaocliente');

        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(16, $usuario['empresa'])->toArray();
        $formAvaliacao = new agendamentoForm('frmAgendamento', $this->getServiceLocator(), $campos);
        $serviceAgendamento = $this->getServiceLocator()->get('Agendamento2');
        
        
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
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(20, $usuario['empresa'])->toArray();
        $formAvaliacao = new comercialForm('frmComercial', $this->getServiceLocator(), $campos);
        $serviceComercial = $this->getServiceLocator()->get('Comercial2');
        
        
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

        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(17, $usuario['empresa'])->toArray();
        
        $formAvaliacao = new processoForm('frmProcesso', $this->getServiceLocator(), $campos);
        $serviceProcesso = $this->getServiceLocator()->get('Processo2');
        
        
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
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(18, $usuario['empresa'])->toArray();
        $formAvaliacao = new qualidadeForm('frmQualidade', $this->getServiceLocator(), $campos);
        $serviceQualidade = $this->getServiceLocator()->get('Qualidade2');
        
        
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
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(19, $usuario['empresa'])->toArray();
        $formAvaliacao = new segurancaForm('frmSeguranca', $this->getServiceLocator(), $campos);
        $serviceSeguranca = $this->getServiceLocator()->get('Seguranca2');
        
        
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