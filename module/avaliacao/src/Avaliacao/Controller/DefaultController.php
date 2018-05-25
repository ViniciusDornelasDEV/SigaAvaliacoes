<?php

namespace Avaliacao\Controller;


use Zend\View\Model\ViewModel;

use Application\Controller\BaseController;

use Avaliacao\Form\Agendamento as agendamentoForm;
use Avaliacao\Form\Processo as processoForm;
use Avaliacao\Form\Qualidade as qualidadeForm;
use Avaliacao\Form\Seguranca as segurancaForm;
use Avaliacao\Form\Ata as ataForm;

class DefaultController extends BaseController
{

	public function avaliacao($form, $service, $idForm, $nomeForm, $proximaRota, $campos = false)
    { 
        $this->layout('layout/avaliacaocliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();

        //Verificações de acesso
        
        //se não vier mes e ano redir para listagem
        if(!$this->params()->fromRoute('mes') || !$this->params()->fromRoute('ano')){
            $this->flashMessenger()->addErrorMessage('Não foi possível encontrar um mês/ano referência!');
            return $this->redirect()->toRoute('avaliacoesEmpresa');
        }

        //verificar se período de avaliação está aberto
        $periodoAvaliacao = $this->verificaPeriodoAvaliacaoAberto($this->params()->fromRoute('mes'), $this->params()->fromRoute('ano'));

        if(!$periodoAvaliacao){
            //redirecionar para listagem de avaliações
            return $this->redirect()->toRoute('avaliacoesEmpresa');
        }

        //verificar se usuário pode responder a este formulário
        $acessoAba = $this->verificaAcessoAba($usuario, $idForm);
            
        //verificar se formulário já foi respondido
        $avaliacaoRespondida = false;
        
        if($this->verificaRespondido($service, $usuario['empresa'], $this->params()->fromRoute('mes'), $this->params()->fromRoute('ano'))){
            $acessoAba = false;
            $avaliacaoRespondida = true;
        }
        //caso venha um post salvar
        $formInvalido = false; 
        if($this->getRequest()->isPost()){
            //salvar
            //Obter dados
            $dados = $this->getRequest()->getPost()->toArray();
            $files = $this->getRequest()->getfiles()->toArray();
            //$serviceProcesso = $this->getServiceLocator()->get('Processo');

            //validar form
            $form->setData($dados);

            if($form->isValid()){
                //criar pasta da avaliação e fazer upload de arquivos

                $date = $this->getPeriodoAvaliacao($periodoAvaliacao->mes_referencia, $periodoAvaliacao->ano_referencia);
                $dir = 'public/arquivos/'.$usuario['empresa'];
                if(!file_exists($dir)){
                    mkdir($dir);
                }

                $dir .= '/'.$date['mes'].'-'.$date['ano'];
                if(!file_exists($dir)){
                    mkdir($dir);
                }
                
                $dir .= '/processo';
                if(!file_exists($dir)){
                    mkdir($dir);
                }
                
                $dados = $this->uploadImagem($files, $dir, $dados);
                $dados['ano'] = $date['ano'];
                $dados['mes'] = $date['mes'];
                $dados['usuario'] = $usuario['id'];
                $dados['empresa'] = $usuario['empresa'];

                $result = $service->insert($dados);
                if($result){
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Formulário '.$nomeForm.' respondido com sucesso!');                
                    return $this->redirect()->toRoute($proximaRota, array('mes' => $this->params()->fromRoute('mes'),
                    													  'ano' => $this->params()->fromRoute('ano')
                    									));
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao salvar formulário '.$nomeForm.'!'); 
                }
            }else{
                $formInvalido = true; 
            }
        }
        return new ViewModel(array('form'   => $form,
                                    'formInvalido'  => $formInvalido,
                                    'acessoAba'      => $acessoAba,
                                    'avaliacaoRespondida' => $avaliacaoRespondida,
                                    'mes_referencia'			=> $this->params()->fromRoute('mes'),
                                    'ano_referencia'			=> $this->params()->fromRoute('ano'),
                                    'campos'            =>      $campos
                            ));
    }

    public function verificaRespondido($service, $empresa, $mes, $ano){
        $mes = (int) $mes;
        $avaliacao = $service->getRecordsFromArray(array('ano' => $ano, 'mes' => $mes, 'empresa' => $empresa))->current();
        
        if($avaliacao){
            return true;
        }
        return false;
        
    }

    //Verificar se admin deu permissão para iniciar as avaliaões 
    public function verificaPeriodoAvaliacaoAberto($mes = false, $ano = false){
        //Verificar se existe uma avaliação liberada para o mês atual
        $servicePilha = $this->getServiceLocator()->get('PilhaAvaliacoes');
        $liberacao = $servicePilha->getAvaliacaoAbertaByDate(date('Y-m-d'), 'C', $mes, $ano)->current();

        //se tiver retorna as liberações
        return $liberacao;
    }

    public function verificaAcessoAba($usuario, $idAba){
        $abasAutorizado = $this->getServiceLocator()->get('AbasUsuario')
                            ->getRecordsFromArray(array('usuario' => $usuario['id'], 'empresa' => $usuario['empresa']));
        foreach ($abasAutorizado as $aba) {
            if($aba->aba == $idAba){
                return true;
            }
        }
        return false;
    }

    public function uploadImagem($arquivos, $caminho, $dados){
        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$nomeArquivo.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$nomeArquivo.'.'.$extensao;
                }
            }
        }

        return $dados;
    }

    //DEFINE SERVICE E ABA PARA LISTAR AVALIAÇÔES
    public function defineService($aba){
        switch ($aba) {
            case 1:
                return $this->getServiceLocator()->get('Agendamento');
            case 2:
                return $this->getServiceLocator()->get('Processo');
            case 3:
                return $this->getServiceLocator()->get('Qualidade');
            case 4:
                return $this->getServiceLocator()->get('Seguranca');
            case 5:
                return $this->getServiceLocator()->get('Ata');
            case 6:
                return $this->getServiceLocator()->get('AvaliacaoQualitativa');
            case 7:
                return $this->getServiceLocator()->get('AvaliacaoPid');
            case 9:
                return $this->getServiceLocator()->get('Comercial');
        }
    }

    public function defineAba($aba){
        switch ($aba) {
            case 1:
                return 'avaliacaoAgendamentoAudicao';
            case 2;
                return 'avaliacaoProcessoAudicao';
            case 3:
                return 'avaliacaoQualidadeAudicao';
            case 4:
                return 'avaliacaoSegurancaAudicao';
            case 5:
                return 'avaliacaoAtaAudicao';
            case 6:
                return 'avaliacaoQualitativaAudicao';
            case 7:
                return 'avaliacaoPidAudicao';
            case 9:
                return 'avaliacaoComercialAudicao';
        }
    }
}

?>