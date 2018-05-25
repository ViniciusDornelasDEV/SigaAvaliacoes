<?php

namespace Avaliacao\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

use Avaliacao\Form\LiberarAvaliacao as formLiberar;
use Usuario\Form\PesquisaEmpresa as pesquisaEmpresaForm;
use Avaliacao\Form\PersonalizarAvaliacao as personalizarForm;

class ConfiguraravaliacaoController extends BaseController
{
	public function listaliberacoesAction(){
		$serviceAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacoes');
		$liberacoes = $serviceAvaliacoes->getRecordsFromArray(array(), 'id DESC');

		$Paginator = new Paginator(new ArrayAdapter($liberacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

		return new ViewModel(array(
				'liberacoes' => $Paginator
			));
	}

	public function inseriravaliacoesAction(){

		$formAvaliacao = new formLiberar('formLiberarAvaliacao');

		if($this->getRequest()->isPost()){
			$dados = $this->getRequest()->getPost();
			$formAvaliacao->setData($dados);

			if($formAvaliacao->isValid()){
				$servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacoes');
				try {
					$res = $servicePilhaAvaliacoes->insert($formAvaliacao->getData());
					$this->flashMessenger()->addSuccessMessage('Período de avaliações inserido com sucesso!');
				} catch (Exception $e) {
					$this->flashMessenger()->addErrorMessage('Erro: '.$e->getMessage());
				}
				$this->redirect()->toRoute('listaLiberacoes');

			}
		}

		return new viewModel(array(
				'formAvaliacao' => $formAvaliacao
			));
	}

	public function alterarliberacaoAction(){
		$idLiberacao = $this->params()->fromRoute('id');
		$formAvaliacao = new formLiberar('formLiberarAvaliacao');
		$servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacoes');

		//pesquisar liberacao
		$formAvaliacao->setData($servicePilhaAvaliacoes->getRecord($idLiberacao));

		if($this->getRequest()->isPost()){
			$dados = $this->getRequest()->getPost();
			$formAvaliacao->setData($dados);
			if($formAvaliacao->isValid()){
				$servicePilhaAvaliacoes->update($formAvaliacao->getData(), array('id' => $idLiberacao));
				$this->flashMessenger()->addSuccessMessage('Período de avaliação alterado com sucesso!');
				return $this->redirect()->toRoute('alterarLiberacao', array('id' => $idLiberacao));
			}
		}

		return new viewModel(array('formAvaliacao' => $formAvaliacao));
	}

	public function liberacaodeletarAction(){
		try {
			$idLiberacao = $this->params()->fromRoute('id');
			$res = $this->getServiceLocator()->get('PilhaAvaliacoes')->delete(array('id' => $idLiberacao));

			$this->flashMessenger()->addSuccessMessage('Período de avaliações excluído com sucesso!');
		} catch (Exception $e) {
			$this->flashMessenger()->addErrorMessage('Erro: '.$e->getMessage());
		}
		$this->redirect()->toRoute('listaLiberacoes');

		$view = new ViewModel();
		$view->setTerminal(true);

		return $view;
	}

	//AVALIAÇÕES PERSONALIZADAS
	public function personalizaravaliacaoAction(){
		//pegar aba como parâmetro
		$idAba = $this->params()->fromRoute('aba');
		$empresa = $this->params()->fromRoute('empresa');
		//pesquisar campos da aba
		$serviceCampoEmpresa = $this->getServiceLocator()->get('CampoEmpresa');
		$campos = $serviceCampoEmpresa->getCamposEmpresaByAba($idAba, $empresa)->toArray();
		
		if(count($campos) == 0){
			$this->flashMessenger()->addErrorMessage('Não existem campos vinculados a esta empresa, por favor contate o administrador!');
			return $this->redirect()->toRoute('empresa');
		}

		//instanciar form de avaliação personalizada
		$formPersonalizar = new personalizarForm('formPersonalizar', $this->getServiceLocator(), $campos, $idAba);
		$aba = $this->getServiceLocator()->get('Aba')->getRecord($idAba);

		//caso venha post salvar alterações
		if($this->getRequest()->isPost()){
			$formPersonalizar->setData($this->getRequest()->getPost());
			if($formPersonalizar->isValid()){
				$dados = $this->getRequest()->getPost();
				
				if($serviceCampoEmpresa->personalizarFormEmpresa($campos, $dados->toArray())){
					$this->flashMessenger()->addSuccessMessage('Avaliação de '.$aba->nome.' personalizada com sucesso!');
				}else{
					$this->flashMessenger()->addErrorMessage('Falha ao personalizar avaliação de '.$aba->nome.'!');
				}
				return $this->redirect()->toRoute('personalizarAvaliacao', array('empresa' => $empresa, 'aba' => $idAba));

			}
		}

		return new ViewModel(array(
								'formAvaliacao'		=>	$formPersonalizar,
								'aba'				=>	$aba,
								'idEmpresa'			=>	$empresa
							));
	}

}