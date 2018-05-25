<?php

namespace Callcenter\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

use Callcenter\Form\Meta as formMeta;
use Callcenter\Form\ReplicarMetaMensal as formReplicarMensal;

class MetaController extends BaseController
{
   public function indexAction(){
        $this->layout('layout/admincallcenter');
        
        $serviceMetaAgendamento = $this->getServiceLocator()->get('MetaAgendamento');
        $metas = $serviceMetaAgendamento->getMetas();
        
        $paginator = new Paginator(new ArrayAdapter($metas->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);
        
        return new ViewModel(array(
                                'metas'      => $paginator
                            ));
   }

   public function novoAction(){
        $this->layout('layout/admincallcenter');

        $formMeta = new formMeta('formMeta', $this->getServiceLocator());

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formMeta->setData($dados);
            if($formMeta->isValid()){
                $serviceMeta = $this->getServiceLocator()->get('MetaAgendamento');
                $serviceMeta->insert($formMeta->getData());
                $this->flashMessenger()->addSuccessMessage('Meta de agendamento inserida com sucesso!');
                return $this->redirect()->toRoute('indexMetasAgendamento');
            }
        }
        return new ViewModel(array('formMeta' => $formMeta));
   }

   public function editarAction(){
        $this->layout('layout/admincallcenter');

        $idMeta = $this->params()->fromRoute('id');
        $serviceMeta = $this->getServiceLocator()->get('MetaAgendamento');
        $meta = $serviceMeta->getRecord($idMeta);

        $formMeta = new formMeta('formMeta', $this->getServiceLocator());
        $formMeta->setData($meta, true);

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formMeta->setData($dados);
            if($formMeta->isValid()){
                $serviceMeta->update($formMeta->getData(), array('id' => $idMeta));
                $this->flashMessenger()->addSuccessMessage('Meta alterada com sucesso!');
                return $this->redirect()->toRoute('indexMetasAgendamento');
            }
        }

        return new ViewModel(array('formMeta' => $formMeta));
   }

   public function deletarAction(){
        $serviceMeta = $this->getServiceLocator()->get('MetaAgendamento');
        $idMeta = $this->params()->fromRoute('id');
        if($serviceMeta->delete(array('id' => $idMeta))){
            $this->flashMessenger()->addSuccessMessage('Meta excluída com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir meta de agendamento!');
        }
        return $this->redirect()->toRoute('indexMetasAgendamento');
   }

    public function listametasmensaisAction(){
        $this->layout('layout/admincallcenter');
        
        $serviceMetaAgendamentoMensal = $this->getServiceLocator()->get('MetaAgendamentoMensal');
        $metas = $serviceMetaAgendamentoMensal->getMetas();
        
        $paginator = new Paginator(new ArrayAdapter($metas->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);
        
        return new ViewModel(array(
                                'metas'      => $paginator
                            ));
    }

    public function novometamensalAction(){
        $this->layout('layout/admincallcenter');

        $formMeta = new formMeta('formMeta', $this->getServiceLocator());

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formMeta->setData($dados);
            if($formMeta->isValid()){
                $serviceMetaMensal = $this->getServiceLocator()->get('MetaAgendamentoMensal');
                $serviceMetaMensal->insert($formMeta->getData());
                $this->flashMessenger()->addSuccessMessage('Meta de agendamento mensal inserida com sucesso!');
                return $this->redirect()->toRoute('indexMetasMensais');
            }
        }
        return new ViewModel(array('formMeta' => $formMeta));
    }

    public function editarmetamensalAction(){
        $this->layout('layout/admincallcenter');

        $idMeta = $this->params()->fromRoute('id');
        $serviceMetaMensal = $this->getServiceLocator()->get('MetaAgendamentoMensal');
        $meta = $serviceMetaMensal->getRecord($idMeta);

        $formMeta = new formMeta('formMeta', $this->getServiceLocator());
        $formMeta->setData($meta, true);

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formMeta->setData($dados);
            if($formMeta->isValid()){
                $serviceMetaMensal->update($formMeta->getData(), array('id' => $idMeta));
                $this->flashMessenger()->addSuccessMessage('Meta mensal alterada com sucesso!');
                return $this->redirect()->toRoute('indexMetasMensais');
            }
        }

        return new ViewModel(array('formMeta' => $formMeta));
    }

    public function deletarmetamensalAction(){
        $serviceMetaMensal = $this->getServiceLocator()->get('MetaAgendamentoMensal');
        $idMeta = $this->params()->fromRoute('id');
        if($serviceMetaMensal->delete(array('id' => $idMeta))){
            $this->flashMessenger()->addSuccessMessage('Meta mensal excluída com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir meta de agendamento mensal!');
        }
        return $this->redirect()->toRoute('indexMetasMensais');
    }

    public function replicarmensalAction(){
        $this->layout('layout/admincallcenter');
        $formReplicar = new formReplicarMensal('formReplicar');

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formReplicar->setData($dados);
            if($formReplicar->isValid()){
                $dados = $formReplicar->getData();

                //enviar para model replicar as metas
                $res = $this->getServiceLocator()->get('MetaAgendamentoMensal')->replicarMetas($dados);
                if($res){
                    $this->flashMessenger()->addSuccessMessage('Metas mensais replicadas com sucesso!');
                }else{
                    $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao replicar metas mensais, por favor tente novamente!');
                }
                return $this->redirect()->toRoute('indexMetasMensais');
            }
        }

        return new ViewModel(array('formReplicar' => $formReplicar));
    }

}

