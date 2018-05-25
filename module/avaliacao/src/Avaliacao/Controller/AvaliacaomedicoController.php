<?php

namespace Avaliacao\Controller;


use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Application\Controller\BaseController;
use Zend\Session\Container;

use Avaliacao\Form\AvaliacaoQualitativaMedico as avaliacaoQualitativaForm;
use Avaliacao\Form\AvaliacaoPidMedico as avaliacaoPidForm;
use Avaliacao\Form\ImprimirAvaliacaoQualitativa as imprimirQualitativaForm;

use Avaliacao\Form\UploadArquivo as uploadArquivoForm;
use Avaliacao\Form\PesquisaMedicos as formPesquisa;
use Avaliacao\Form\PesquisaMedicosRespondida as formPesquisaRespondidas;

class AvaliacaomedicoController extends BaseController
{

    public function indexAction()
    {
        $this->layout('layout/medico'); 
        //Recuperar dados de usuário
        $usuario = $this->getServiceLocator()->get('session')->read();

        //parametros da pesquisa
        $formPesquisa = new formPesquisa('frmPosquisa', $this->getServiceLocator());
        $sessao = new Container();

        if(!isset($sessao->parametros)){
            $sessao->parametros = array('respondida' => '');
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                //redir para mesma página
                unset($sessao->parametros);
                return $this->redirect()->toRoute('medicoIndex');
            }else{
                //paràmetros da pesquisa
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $sessao->parametros = $formPesquisa->getData();
                }
            }
        }
        
        $formPesquisa->setData($sessao->parametros);
        
        //pesquisar avaliações em aberto para médico logado no sistema
        $periodosAvaliacao = $this->getServiceLocator()->get('PilhaAvaliacoes')->getAvaliacaoAbertaByDate(date('Y-m-d'), 'M');

        
        $serviceAvaliacaoQualitativa = $this->getServiceLocator()->get('AvaliacaoQualitativa');       
        //criar 1 avaliação para cada médico no array avaliações
        $avaliacoesAbertas = array();
        foreach ($periodosAvaliacao as $periodo) {
            $avaliacoes = $serviceAvaliacaoQualitativa
                            ->getAvaliacoesRespondidasByPeriodo($usuario['id'], $periodo->mes_referencia, $periodo->ano_referencia, $sessao->parametros);
            
            foreach ($avaliacoes as $avaliacao) {
                 $avaliacoesAbertas[] = array(
                                'nome_empresa'      =>  $avaliacao->nome_empresa,
                                'nome_medico'       =>  $avaliacao->nome_medico,
                                'mes_referencia'    =>  $periodo->mes_referencia,
                                'ano_referencia'    =>  $periodo->ano_referencia,
                                'data_fechamento'   =>  $periodo->termino,
                                'id_medico'         =>  $avaliacao->id_medico,
                                'id_periodo'        =>  $periodo->id,
                                'respondida'        =>  $avaliacao->finalizada
                            );
            }
        }





        return new ViewModel(array(
                            'avaliacoes'        =>  $avaliacoesAbertas,
                            'diretorMedico'     =>  $usuario,
                            'formPesquisa'      =>  $formPesquisa,
                        ));
    }

    public function medicoavaliacaoqualitativaAction(){
        $this->layout('layout/medico');
        $usuario = $this->getServiceLocator()->get('session')->read();

        $idMedico = $this->params()->fromRoute('medico');
        $medico = $this->getServiceLocator()->get('Medico')->getRecord($idMedico);

        //pesquisar campos da avaliação para a empresa
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(6, $medico->empresa);

        $formAvaliacao = new avaliacaoQualitativaForm('formAvaliacao', $this->getServiceLocator(), $campos->toArray());

        //pesquisar período de avaliação
        $periodoAvaliacao = $this->getServiceLocator()->get('PilhaAvaliacoes')->getRecord($this->params()->fromRoute('periodo'));

        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoQualitativa');
        //pesquisar avaliacao
        $avaliacao = $serviceAvaliacao->getRecordFromArray(array(
                        'ano'       => $periodoAvaliacao['ano_referencia'],
                        'mes'       => $periodoAvaliacao['mes_referencia'],
                        'medico'    => $medico->id
                    ));


        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formAvaliacao->setData($dados);

            if($formAvaliacao->isValid()){
                $dados = $formAvaliacao->getData();
                //inserir avaliação qualitativa para usuário
                $dados['ano'] = $periodoAvaliacao->ano_referencia;
                $dados['mes'] = $periodoAvaliacao->mes_referencia;
                $dados['usuario'] = $usuario['id'];
                $dados['empresa'] = $medico->empresa;
                $dados['medico'] = $medico->id;
                
                if($avaliacao){
                    //alterar
                    $serviceAvaliacao->update($dados, array('id' => $avaliacao->id));
                    $this->flashMessenger()->addSuccessMessage('Avaliação qualitativa alterada com sucesso!');
                }else{
                    //inserir
                    $serviceAvaliacao->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Avaliação qualitativa inserida com sucesso!');
                }
                return $this->redirect()->toRoute('medicoPid', array('medico' => $medico->id, 'periodo' => $periodoAvaliacao->id));

            }
        }

        
        //se existe avaliação popular form
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
        }

        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($medico->empresa);
        return new ViewModel(array(
                'formAvaliacao'     => $formAvaliacao,
                'empresa'           => $empresa,
                'periodo'           => $periodoAvaliacao,
                'medico'            => $medico,
                'avaliacao'         => $avaliacao
            ));
    }

    public function medicopidAction(){
        $this->layout('layout/medico');
        $usuario = $this->getServiceLocator()->get('session')->read();

        $idMedico = $this->params()->fromRoute('medico');
        $medico = $this->getServiceLocator()->get('Medico')->getRecord($idMedico);
        //pesquisar campos da avaliação para a empresa
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(7, $medico->empresa);
        $formAvaliacao = new avaliacaoPidForm('formAvaliacao', $campos->toArray());

        //instanciar form de arquivo
        $formArquivo = new uploadArquivoForm('formArquivo');

        //pesquisar período de avaliação
        $periodoAvaliacao = $this->getServiceLocator()->get('PilhaAvaliacoes')->getRecord($this->params()->fromRoute('periodo'));

        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoPid');
        
        //pesquisar avaliacao
        $avaliacao = $serviceAvaliacao->getRecordFromArray(array(
                        'ano'       => $periodoAvaliacao['ano_referencia'],
                        'mes'       => $periodoAvaliacao['mes_referencia'],
                        'medico'    => $medico->id
                    ));


        //Verificar se deve exibir botão FINALIZAR AVALIAÇÃO por mês/ano, empresa e diretor médico
        $botaoFinalizar = false;
        $serviceAvaliacaoQualitativa = $this->getServiceLocator()->get('AvaliacaoQualitativa');


        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();            
            $formAvaliacao->setData($dados);

            //caso venha um arquivo fazer upload dele
            $files = $this->getRequest()->getfiles()->toArray();
            if($formAvaliacao->isValid() || isset($files)){
                $dados = $dados->toArray();
                //inserir avaliação qualitativa para usuário
                $dados['ano'] = $periodoAvaliacao->ano_referencia;
                $dados['mes'] = $periodoAvaliacao->mes_referencia;
                $dados['usuario'] = $usuario['id'];
                $dados['empresa'] = $medico->empresa;
                $dados['medico'] = $medico->id;

                if(isset($files['arquivo'])){
                    if(empty($files['arquivo']['name'])){
                        $this->flashMessenger()->addErrorMessage('Por favor insira um arquivo!');
                        return $this->redirect()->toRoute('medicoPid', array('medico' => $medico->id, 'periodo' => $periodoAvaliacao->id));
                    }

                    $dir = 'public/arquivos/'.$medico->empresa.'/pid';
                    $dados = $this->uploadImagem($files, $dir, $dados);
                }

                if($avaliacao){
                    //alterar
                    $serviceAvaliacao->update($dados, array('id' => $avaliacao->id));
                    $this->flashMessenger()->addSuccessMessage('Avaliação PID alterada com sucesso!');
                }else{
                    //inserir
                    $serviceAvaliacao->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Avaliação PID inserida com sucesso!');
                }

                //finalizar avaliacao
                if(isset($dados['salvar'])){
                    //voltar para pid
                    return $this->redirect()->toRoute('medicoPid', array('medico' => $medico->id, 'periodo' => $periodoAvaliacao->id));
                }else{
                    return $this->finalizaravaliacoes($medico, $periodoAvaliacao);
                }

            }
        }

        //se existe avaliação popular form
        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
        }

        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($medico->empresa);
        return new ViewModel(array(
            'formAvaliacao'     => $formAvaliacao,
            'avaliacao'         => $avaliacao,
            'periodo'           => $periodoAvaliacao,
            'medico'            => $medico,
            'formArquivo'       => $formArquivo
        ));
    }

    public function imprimiravaliacaoqualitativaAction(){
        $this->layout('layout/limpo');

        $idMedico = $this->params()->fromRoute('medico');
        $medico = $this->getServiceLocator()->get('Medico')->getRecord($idMedico);
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(6, $medico->empresa);
        $formAvaliacao = new imprimirQualitativaForm('formAvaliacao', $this->getServiceLocator(), $campos->toArray());

        //pesquisar avaliacao
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');
        $avaliacao = $this->getServiceLocator()->get('AvaliacaoQualitativa')->getRecord($idAvaliacao);

        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
        }

        $periodoAvaliacao = $this->getServiceLocator()->get('PilhaAvaliacoes')->getRecord($this->params()->fromRoute('periodo'));

        
        return new ViewModel(array(
                'formAvaliacao' => $formAvaliacao,
                'periodo'       => $periodoAvaliacao,
                'medico'        => $medico

            ));
    }

    public function imprimiravaliacaopidAction(){
        $this->layout('layout/limpo');

        $idMedico = $this->params()->fromRoute('medico');
        $medico = $this->getServiceLocator()->get('Medico')->getRecord($idMedico);
        
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(7, $medico->empresa);
        
        $formAvaliacao = new avaliacaoPidForm('formAvaliacao', $campos->toArray());

        //pesquisar avaliacao
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');
        $avaliacao = $this->getServiceLocator()->get('AvaliacaoPid')->getRecord($idAvaliacao);

        if($avaliacao){
            $formAvaliacao->setData($avaliacao);
        }

        $periodoAvaliacao = $this->getServiceLocator()->get('PilhaAvaliacoes')->getRecord($this->params()->fromRoute('periodo'));

        
        return new ViewModel(array(
                'formAvaliacao' => $formAvaliacao,
                'periodo'       => $periodoAvaliacao,
                'medico'        => $medico

            ));
    }

    public function listaravaliacoesmedicoAction(){
        
        $usuario = $this->getServiceLocator()->get('session')->read();

        //pesquisar avaliações
        $avaliacoes = $this
                        ->getServiceLocator()->get('AvaliacaoQualitativa')
                        ->getRecordsFromArray(array('medico' => $usuario['medico']), 'ano DESC, mes DESC');

        $Paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        return new ViewModel(array(
                'avaliacoes' => $Paginator
            ));
    }

    private function finalizaravaliacoes($medico, $periodo){
        $serviceQualitativa = $this->getServiceLocator()->get('AvaliacaoQualitativa');
 
        $servicePid = $this->getServiceLocator()->get('AvaliacaoPid');
        
        $params = array(
                'medico' => $medico->id,
                'ano'    => $periodo->ano_referencia,
                'mes'    => $periodo->mes_referencia
            );

        $servicePid->update(array('finalizada' => 'S'), $params);
        $serviceQualitativa->update(array('finalizada' => 'S'), $params);


        $this->flashMessenger()->addSuccessMessage('Avaliações finalizadas com sucesso!');
        return $this->redirect()->toRoute('medicoIndex');

        return new ViewModel();        
    }

    private function verificaAbertas($avaliacoes){
        $abertas = 0;
        foreach ($avaliacoes as $avaliacao) {
            if(empty($avaliacao->id_avaliacao)) {
                $abertas++;
            }
        }

        return $abertas;
    }

    public function indexfinalizadasAction(){
        $this->layout('layout/medico'); 
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoQualitativa');
        $pesquisaForm = new formPesquisaRespondidas('formPesquisa');
        $sessao = new Container();

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                //redir para mesma página
                unset($sessao->parametros);
                return $this->redirect()->toRoute('indexFinalizadas');
            }else{
                //paràmetros da pesquisa
                $sessao->parametros = $dados;
            }
        }
        $parametros = array();
        
        if(isset($sessao->parametros)) {
            $pesquisaForm->setData($sessao->parametros);
            if(isset($sessao->parametros['mes']) && !empty($sessao->parametros['mes'])){
                $parametros['mes'] = $sessao->parametros['mes'];
            }
            if(isset($sessao->parametros['ano']) && !empty($sessao->parametros['ano'])){
                $parametros['ano'] = $sessao->parametros['ano'];
            }
        }else{
            $sessao->parametros = array();
        }


        $usuario = $this->getServiceLocator()->get('session')->read();
        $avaliacoes = $serviceAvaliacao->getAvaliacoesRespondidasByEmpresas($parametros, $usuario['id']);

        //Cria paginacao
        $Paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        return new ViewModel(array(
                                'formPesquisa'      => $pesquisaForm,
                                'avaliacoes'        => $Paginator,
                                'diretorMedico'     => $usuario
                            ));
    }

    public function qualitativafinalizadaAction(){

    }

    public function pidfinalizadaAction(){

    }
}

?>