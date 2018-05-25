<?php
namespace Audicao\Controller;

use Avaliacao\Controller\DefaultController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Audicao\Form\PesquisaAvaliacaoAudicao as pesquisaForm;

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


class AudicaoController extends DefaultController
{

    public function indexAction()
    {
        $this->layout('layout/avaliacaoindex');
        $pesquisaForm = new pesquisaForm('formPesquisa', $this->getServiceLocator());

        $sessao = new Container();
        $parametros = array('auditado' => 'N');

        $serviceAba = $this->getServiceLocator()->get('Agendamento');
        $aba = 1;
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                //redir para mesma página
                unset($sessao->parametros);
                return $this->redirect()->toRoute('avaliacoesaudicaonovo');
            }else{
                //paràmetros da pesquisa
                $serviceAba = $this->defineService($dados->aba);
                $aba = $dados->aba;
                $sessao->parametros = $dados;
            }
        }
        
        if(isset($sessao->parametros->aba)){
            $aba = $sessao->parametros->aba;
        }
        if($aba == 5){
            $parametros = array();
            $date = $this->getPeriodoAvaliacao(date('m'), date('Y'));
            if(empty($sessao->parametros->mes)){
                $parametros['mes'] = $date['mes'];
            }
            if(empty($sessao->parametros->ano)){
                $parametros['ano'] = $date['ano'];
            }
        }

        if(isset($sessao->parametros)) {
            $serviceAba = $this->defineService($sessao->parametros->aba);

            //se for avaliação do médico verificar se foi CONFIRMADA
            if($sessao->parametros->aba == 6 || $sessao->parametros->aba == 7){
                $parametros['finalizada'] = 'S';
            }

            if($sessao->parametros->aba)
            $pesquisaForm->setData($sessao->parametros);
            if(!empty($sessao->parametros->mes)){
                $parametros['mes'] = $sessao->parametros->mes;
            }
            if(!empty($sessao->parametros->ano)){
                $parametros['ano'] = $sessao->parametros->ano;
            }
            if(!empty($sessao->parametros->empresa)){
                $parametros['empresa'] = $sessao->parametros->empresa;
            }
        }
        
        //Pesquisa avaliações
        $avaliacoesRespondidas = true;
        $ano = false;
        $mes = false;
        if(isset($sessao->parametros->respondida) && $sessao->parametros->respondida == 'N'){
            
            if($aba > 5){
                $sessao = new Container();
                unset($sessao->parametros);
                $this->flashMessenger()->addWarningMessage('- Auditor pode responder apenas a avaliações de clínicas!');
                return $this->redirect()->toRoute('avaliacoesaudicaonovo');
            }

            $avaliacoesRespondidas = false;
            if(empty($sessao->parametros->ano) || empty($sessao->parametros->mes) || empty($sessao->parametros->aba)){
                unset($sessao->parametros);
                $this->flashMessenger()->addWarningMessage('Ano, mês e formulário devem ser informados para listagem de avaliações não respondidas!');
                return $this->redirect()->toRoute('avaliacoesaudicaonovo');
            }

            $empresa = false;
            $ano = $sessao->parametros->ano;
            $mes = $sessao->parametros->mes;
            if(!empty($sessao->parametros->empresa)){
                $empresa = $sessao->parametros->empresa;
            }
            $avaliacoes = $serviceAba->getavaliacoesNaoRespondidas($sessao->parametros->ano, $sessao->parametros->mes, $aba, $empresa);
        }else{
            $avaliacoes = $serviceAba->getavaliacoesAuditar($parametros);
        }
        $nomeAba = $this->defineAba($aba);
        
        //Cria paginacao
        $Paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        return new ViewModel(array(
                                'formPesquisa'              => $pesquisaForm,
                                'avaliacoes'                => $Paginator,
                                'aba'                       => $nomeAba,
                                'numeroAba'                 => $aba,
                                'avaliacoesRespondidas'     => $avaliacoesRespondidas,
                                'ano'                       => $ano,
                                'mes'                       => $mes,           
                            ));
    }

    public function agendamentoAction()
    { 
        $this->layout('layout/avaliacaocliente');

        //pesquisar avaliacao
        $serviceAgendamento = $this->getServiceLocator()->get('Agendamento');
        $avaliacao = $serviceAgendamento->getRecord($this->params()->fromRoute('id'));

        //pesquisar campos para formulário
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(1, $avaliacao->empresa)->toArray();
        $formAgendamento = new agendamentoForm('frmAgendamento', $this->getServiceLocator(), $campos);
        
        //verificar se audição já foi realizada
        $audicaoRealizada = false;
        if($serviceAgendamento->getRecord($this->params()->fromRoute('id'), 'id_formulario')){
            //Avaliação já foi auditada
            $audicaoRealizada = true;
        }else{
            //popular form
            $formAgendamento->setData($avaliacao);
            //caso venha um post salvar
            if($this->getRequest()->isPost()){
                //validar form
                $dados = $this->getRequest()->getPost()->toArray();
                $formAgendamento->setData($dados);
                if($formAgendamento->isValid()){

                    //inserir audição na base de dados  
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    if($serviceAgendamento->insertAudicao($dados, $avaliacao['id'], $usuario['id'])){
                        //sucesso criar mensagem e redir para edit
                        $this->flashMessenger()->addSuccessMessage('Formulário de agendamento auditado com sucesso!');                
                        return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                    }else{
                        //falha, exibir mensagem de erro
                        $this->flashMessenger()->addErrorMessage('Falha ao auditar formulário!'); 
                    }
                }
            }
        }

    	return new ViewModel(array('formAgendamento' => $formAgendamento,
                                    'audicaoRealizada' => $audicaoRealizada,
                                    'avaliacao'         => $avaliacao,
                                    'campos'            => $campos
                            ));
    }

    public function comercialAction()
    { 

        $this->layout('layout/avaliacaocliente');

        //pesquisar avaliacao
        $serviceComercial = $this->getServiceLocator()->get('Comercial');
        $avaliacao = $serviceComercial->getRecord($this->params()->fromRoute('id'));

        //pesquisar campos para formulário
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(9, $avaliacao->empresa)->toArray();
        $formComercial = new comercialForm('frmComercial', $this->getServiceLocator(), $campos);
        
        //verificar se audição já foi realizada
        $audicaoRealizada = false;
        if($serviceComercial->getRecord($this->params()->fromRoute('id'), 'id_formulario')){
            //Avaliação já foi auditada
            $audicaoRealizada = true;
        }else{
            //popular form
            $formComercial->setData($avaliacao);
            //caso venha um post salvar
            if($this->getRequest()->isPost()){
                //validar form
                $dados = $this->getRequest()->getPost()->toArray();
                $formComercial->setData($dados);
                if($formComercial->isValid()){

                    //inserir audição na base de dados  
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    if($serviceComercial->insertAudicao($dados, $avaliacao['id'], $usuario['id'])){
                        //sucesso criar mensagem e redir para edit
                        $this->flashMessenger()->addSuccessMessage('Formulário comercial auditado com sucesso!');                
                        return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                    }else{
                        //falha, exibir mensagem de erro
                        $this->flashMessenger()->addErrorMessage('Falha ao auditar formulário!'); 
                    }
                }
            }
        }

        return new ViewModel(array('formComercial' => $formComercial,
                                    'audicaoRealizada' => $audicaoRealizada,
                                    'avaliacao'         => $avaliacao,
                                    'campos'            => $campos
                            ));
    }

    public function processoAction()
    { 
        $this->layout('layout/avaliacaocliente');
        $serviceProcesso = $this->getServiceLocator()->get('Processo');
        $avaliacao = $serviceProcesso->getRecord($this->params()->fromRoute('id'));
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(2, $avaliacao->empresa)->toArray();
        $formProcesso = new processoForm('frmProcesso', $this->getServiceLocator(), $campos);
        
        //verificar se audição já foi realizada
        $audicaoRealizada = false;
        if($serviceProcesso->getRecord($this->params()->fromRoute('id'), 'id_formulario')){
            //Avaliação já foi auditada
            $audicaoRealizada = true;
        }else{
            //popular form
            $formProcesso->setData($avaliacao);
            //caso venha um post salvar
            if($this->getRequest()->isPost()){
                //validar form
                $dados = $this->getRequest()->getPost()->toArray();
                $formProcesso->setData($dados);
                if($formProcesso->isValid()){

                    //inserir audição na base de dados  
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    if($serviceProcesso->insertAudicao($dados, $avaliacao['id'], $usuario['id'])){
                        //sucesso criar mensagem e redir para edit
                        $this->flashMessenger()->addSuccessMessage('Formulário de processo auditado com sucesso!');                
                        return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                    }else{
                        //falha, exibir mensagem de erro
                        $this->flashMessenger()->addErrorMessage('Falha ao auditar formulário!'); 
                    }
                }
            }
        }

        return new ViewModel(array('formProcesso' => $formProcesso,
                                    'audicaoRealizada' => $audicaoRealizada,
                                    'avaliacao'         => $avaliacao,
                                    'campos'        => $campos
                            ));
    }

    public function qualidadeAction()
    { 
        $this->layout('layout/avaliacaocliente');
        $serviceQualidade = $this->getServiceLocator()->get('Qualidade');
        $avaliacao = $serviceQualidade->getRecord($this->params()->fromRoute('id'));
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(3, $avaliacao->empresa)->toArray();
        $formQualidade = new qualidadeForm('frmQualidade', $this->getServiceLocator(), $campos);
        
        //verificar se audição já foi realizada
        $audicaoRealizada = false;
        if($serviceQualidade->getRecord($this->params()->fromRoute('id'), 'id_formulario')){
            //Avaliação já foi auditada
            $audicaoRealizada = true;
        }else{
            //Pesquisar avaliacao e popular form
            $formQualidade->setData($avaliacao);
            //caso venha um post salvar
            if($this->getRequest()->isPost()){
                //validar form
                $dados = $this->getRequest()->getPost()->toArray();
                $formQualidade->setData($dados);
                if($formQualidade->isValid()){

                    //inserir audição na base de dados  
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    if($serviceQualidade->insertAudicao($dados, $avaliacao['id'], $usuario['id'])){
                        //sucesso criar mensagem e redir para edit
                        $this->flashMessenger()->addSuccessMessage('Formulário de qualidade auditado com sucesso!');                
                        return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                    }else{
                        //falha, exibir mensagem de erro
                        $this->flashMessenger()->addErrorMessage('Falha ao auditar formulário!'); 
                    }
                }
            }
        }

        return new ViewModel(array('formQualidade' => $formQualidade,
                                    'audicaoRealizada' => $audicaoRealizada,
                                    'avaliacao'         => $avaliacao,
                                    'campos'     =>   $campos
                            ));
    }

    public function segurancaAction()
    { 
        $this->layout('layout/avaliacaocliente');
        $serviceSeguranca = $this->getServiceLocator()->get('Seguranca');
        $avaliacao = $serviceSeguranca->getRecord($this->params()->fromRoute('id'));
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(4, $avaliacao->empresa)->toArray();
        $formSeguranca = new segurancaForm('frmSeguranca', $this->getServiceLocator(), $campos);
        
        //verificar se audição já foi realizada
        $audicaoRealizada = false;
        if($serviceSeguranca->getRecord($this->params()->fromRoute('id'), 'id_formulario')){
            //Avaliação já foi auditada
            $audicaoRealizada = true;
        }else{
            //Pesquisar avaliacao e popular form
            $formSeguranca->setData($avaliacao);
            //caso venha um post salvar
            if($this->getRequest()->isPost()){
                //validar form
                $dados = $this->getRequest()->getPost()->toArray();
                $formSeguranca->setData($dados);
                if($formSeguranca->isValid()){

                    //inserir audição na base de dados  
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    if($serviceSeguranca->insertAudicao($dados, $avaliacao['id'], $usuario['id'])){
                        //sucesso criar mensagem e redir para edit
                        $this->flashMessenger()->addSuccessMessage('Formulário de segurança auditado com sucesso!');                
                        return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                    }else{
                        //falha, exibir mensagem de erro
                        $this->flashMessenger()->addErrorMessage('Falha ao auditar formulário!'); 
                    }
                }
            }
        }

        return new ViewModel(array('formSeguranca' => $formSeguranca,
                                    'audicaoRealizada' => $audicaoRealizada,
                                    'avaliacao'         => $avaliacao,
                                    'campos'        => $campos
                            ));
    }

    public function ataAction(){
        $this->layout('layout/avaliacaocliente');
        $formAvaliacao = new ataForm('frmAta');
        $serviceAta = $this->getServiceLocator()->get('Ata');
        
        //pesquisar avaliação
        $avaliacao = $serviceAta->getRecordFromArray(
                                                    array(
                                                        'empresa' => $this->params()->fromRoute('empresa'), 
                                                        'ano' => $this->params()->fromRoute('ano'), 
                                                        'mes' => $this->params()->fromRoute('mes')
                                                    )
                                            );
        $formAvaliacao->setData($avaliacao);
            
        foreach ($formAvaliacao as $field) {
            $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
        }
    
        //popular form
        return new ViewModel(array('formAvaliacao' => $formAvaliacao,
                                    'avaliacao'         => $avaliacao,
                                    'empresa'           => $this->params()->fromRoute('empresa'),
                                    'ano'               => $this->params()->fromRoute('ano'),
                                    'mes'               => $this->params()->fromRoute('mes')
                            ));
    }

    public function qualitativamedicoAction(){
        $this->layout('layout/medico');

        $serviceQualitativa = $this->getServiceLocator()->get('AvaliacaoQualitativa');
        $avaliacao = $serviceQualitativa->getRecord($this->params()->fromRoute('id'));

        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(6, $avaliacao->empresa);
        $formQualitativa = new avaliacaoQualitativaForm('formAvaliacao', $this->getServiceLocator(), $campos->toArray());
        
        //verificar se audição já foi realizada
        if($serviceQualitativa->getRecord($this->params()->fromRoute('id'), 'id_formulario')){
            //Avaliação já foi auditada
            $this->flashMessenger()->addWarningMessage('Avaliação já foi auditada!');
            return $this->redirect()->toRoute('avaliacoesaudicaonovo');
        }else{
            //Pesquisar avaliacao e popular form
            $formQualitativa->setData($avaliacao);
            //caso venha um post salvar
            if($this->getRequest()->isPost()){
                //validar form
                $dados = $this->getRequest()->getPost()->toArray();
                $formQualitativa->setData($dados);
                if($formQualitativa->isValid()){

                    //inserir audição na base de dados  
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    if($serviceQualitativa->insertAudicao($dados, $avaliacao['id'], $usuario['id'])){
                        //sucesso criar mensagem e redir para edit
                        $this->flashMessenger()->addSuccessMessage('Avaliação qualitativa auditada com sucesso!');                
                        return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                    }else{
                        //falha, exibir mensagem de erro
                        $this->flashMessenger()->addErrorMessage('Falha ao auditar formulário!'); 
                    }
                }
            }
        }

        $medico = $this->getServiceLocator()->get('Medico')->getRecord($avaliacao->medico);
        return new ViewModel(array('formQualitativa' => $formQualitativa,
                                    'avaliacao'         => $avaliacao,
                                    'medico'            => $medico
                            ));
    }

    public function pidmedicoAction(){
        $this->layout('layout/medico');

        $servicePid = $this->getServiceLocator()->get('AvaliacaoPid');
        $avaliacao = $servicePid->getRecord($this->params()->fromRoute('id'));
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(7, $avaliacao->empresa)->toArray();

        $formPid = new avaliacaoPidForm('formAvaliacao', $campos);
        
        //verificar se audição já foi realizada
        if($servicePid->getRecord($this->params()->fromRoute('id'), 'id_formulario')){
            //Avaliação já foi auditada
            $this->flashMessenger()->addWarningMessage('Avaliação já foi auditada!');
            return $this->redirect()->toRoute('avaliacoesaudicaonovo');
        }else{
            //Pesquisar avaliacao e popular form
            $formPid->setData($avaliacao);
            //caso venha um post salvar
            if($this->getRequest()->isPost()){
                //validar form
                $dados = $this->getRequest()->getPost()->toArray();
                $formPid->setData($dados);
                if($formPid->isValid()){

                    //inserir audição na base de dados  
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    if($servicePid->insertAudicao($dados, $avaliacao['id'], $usuario['id'])){
                        //sucesso criar mensagem e redir para edit
                        $this->flashMessenger()->addSuccessMessage('Avaliação PID auditada com sucesso!');                
                        return $this->redirect()->toRoute('avaliacoesaudicaonovo');
                    }else{
                        //falha, exibir mensagem de erro
                        $this->flashMessenger()->addErrorMessage('Falha ao auditar formulário!'); 
                    }
                }
            }
        }

        $medico = $this->getServiceLocator()->get('Medico')->getRecord($avaliacao->medico);
        return new ViewModel(array('formPid' => $formPid,
                                    'avaliacao'         => $avaliacao,
                                    'medico'            => $medico
                            ));
    }

    public function responderavaliacaoAction(){
        $aba = $this->params()->fromRoute('aba');
        $empresa = $this->params()->fromRoute('empresa');
        $ano = $this->params()->fromRoute('ano');
        $mes = $this->params()->fromRoute('mes');

        $usuario = $this->getServiceLocator()->get('session')->read();

        //criar avaliação default
        $serviceAvaliacao = $this->defineService($aba);
        $idAvaliacao = $serviceAvaliacao->insert(array('empresa' => $empresa, 'ano' => $ano, 'mes' => $mes, 'usuario' => $usuario['id']));

        //redirecionar para interface de auditoria da avaliação
        $nomeRota = $this->defineAba($aba);
        $this->flashMessenger()->addSuccessMessage('- Avaliação em branco inserida, favor auditar avaliação!');

        //se for ata preciso de enviar mes ano empresa
        if($aba == 5){
            return $this->redirect()->toRoute($nomeRota, array('mes' => $mes, 'ano' => $ano, 'empresa' => $empresa));    
        }
        return $this->redirect()->toRoute($nomeRota, array('id' => $idAvaliacao));
    }
}
?>