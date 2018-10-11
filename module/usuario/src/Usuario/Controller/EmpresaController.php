<?php

namespace Usuario\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Usuario\Form\Empresa as empresaForm;
use Usuario\Form\EmpresaArquivos as arquivoForm;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Usuario\Form\PesquisaEmpresa as pesquisaEmpresaForm;


class EmpresaController extends BaseController
{

    public function indexAction()
    {
        //filtrar empresa
        $formPesquisa = new pesquisaEmpresaForm('formPesquisa');

        $sessao = new Container();


        $dados = array();
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                unset($sessao->parametros);
                return $this->redirect()->toRoute('empresa');
            }
            $sessao->parametros = $dados;
            $formPesquisa->setData($dados);
        }else{
            if(isset($sessao->parametros)){
                $dados = $sessao->parametros;
                $formPesquisa->setData($dados);
            }
        }


        $serviceEmpresa = $this->getServiceLocator()->get('Empresa');
        $empresas = $serviceEmpresa->getEmpresasByParams($dados);

        $Paginator = new Paginator(new ArrayAdapter($empresas->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);
        
        return new ViewModel(array(
                                'empresas'      => $Paginator,
                                'formPesquisa'  => $formPesquisa
                            ));
    }

    public function novoAction()
    { 
    	$formEmpresa = new empresaForm('formEmpresa', $this->getServiceLocator());
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $serviceEmpresa = $this->getServiceLocator()->get('Empresa');

            //validar form
            $formEmpresa->setData($dados);
            if($formEmpresa->isValid()){
                $result = $serviceEmpresa->insert($formEmpresa->getData());
                if($result){
                    if(!file_exists('public/arquivos/'.$result)){
                        mkdir('public/arquivos/'.$result);
                    }
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Empresa inserida com sucesso!');                
                    return $this->redirect()->toRoute('empresaAlterar', array('id' => $result));
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir empresa!'); 
                }
            }
        }

    	return new ViewModel(array('formEmpresa' => $formEmpresa));
    }

    public function alterarAction(){
        //Pesquisar empresa
        $idEmpresa = $this->params()->fromRoute('id');
        $serviceEmpresa = $this->getServiceLocator()->get('Empresa');
        $empresa = $serviceEmpresa->getRecordFromArray(array('id' => $idEmpresa));

        //Popular form
        $formEmpresa = new empresaForm('formEmpresa', $this->getServiceLocator());
        $formEmpresa->setData($empresa);

        //form upload de imagem
        $formArquivo = new arquivoForm('formArquivo');

        $serviceArquivo = $this->getServiceLocator()->get('EmpresaArquivo');
        $arquivos = $serviceArquivo->getRecords($idEmpresa, 'empresa', array('*'), 'nome');

        //Alterar empresa
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            $files = $this->getRequest()->getfiles()->toArray();

            //form salvar upload de arquivo
            if(isset($files['arquivo'])){
                if(empty($files['arquivo']['name'])){
                    $this->flashMessenger()->addErrorMessage('Por favor insira um arquivo!');
                    return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
                }
                $usuario = $this->getServiceLocator()->get('session')->read();
                $dir = 'public/arquivos/'.$empresa->id.'/arquivos';
                $dados = $this->uploadImagem($files, $dir, $dados);
                $dados['empresa'] = $empresa->id;
                if($serviceArquivo->insert($dados)){
                    $this->flashMessenger()->addSuccessMessage('Arquivo vinculado com sucesso!');
                }else{
                    $this->flashMessenger()->addErrorMessage('Erro ao vncular arquivo!');
                }
                return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
            }else{
                $formEmpresa->setData($dados);
                if($formEmpresa->isValid()){
                    //alterar empresa
                    if($serviceEmpresa->update($dados, array('id'  =>  $empresa->id))){
                        $this->flashMessenger()->addSuccessMessage('Empresa alterada com sucesso!'); 
                    }else{
                        $this->flashMessenger()->addErrorMessage('Erro ao alterar empresa!');
                    }
                    return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
                }
            }
        }

    	return new ViewModel(array(
                                'formEmpresa' => $formEmpresa,
                                'empresa'     => $empresa,
                                'formArquivo' => $formArquivo,
                                'arquivos'    => $arquivos
                                )
                            );
    }

    public function replicararquivoAction(){
        $serviceArquivo = $this->getServiceLocator()->get('EmpresaArquivo');
        $arquivo = $serviceArquivo->getRecord($this->params()->fromRoute('id'));
        $result = $serviceArquivo->replicarArquivos($arquivo);

        if($result){
            $this->flashMessenger()->addSuccessMessage('Arquivos replicados para '. $result.' empresas!');
        }else{
            $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao replicar arquivos, por favor tente novamente!');
        }

        return $this->redirect()->toRoute('empresaAlterar', array('id' => $arquivo->empresa));
    }

    public function deletarempresaAction(){
    	$serviceEmpresa = $this->getServiceLocator()->get('Empresa');

        $res = $serviceEmpresa->update(array('ativo' => 'N'), array('id' => $this->params()->fromRoute('id')));
        
        if($res){
           $this->flashMessenger()->addSuccessMessage('Empresa excluída com sucesso!');  
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir empresa!');
        }
        return $this->redirect()->toRoute('empresa');
    }

    public function deletararquivoAction(){
        $serviceArquivo = $this->getServiceLocator()->get('EmpresaArquivo');
        $arquivo = $serviceArquivo->getRecord($this->params()->fromRoute('id'));


        if($this->params()->fromRoute('todos') == 'S'){
            //deletar todos
            if (file_exists($arquivo->arquivo)) {
                unlink($arquivo->arquivo);
            }
            $res = $serviceArquivo->delete(array('arquivo' => $arquivo['arquivo']));
        }else{
            $res = $serviceArquivo->delete(array('id' => $this->params()->fromRoute('id')));
        }

        if($res){
           $this->flashMessenger()->addSuccessMessage('Arquivo excluído com sucesso!');  
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir arquivo!');
        }
        return $this->redirect()->toRoute('empresaAlterar', array('id' => $arquivo->empresa));
    }

    public function downloadarquivoAction(){
        $idArquivo = $this->params()->fromRoute('id');
        $arquivo = $this->getServiceLocator()->get('EmpresaArquivo')->getRecord($idArquivo);

        $fileName = $arquivo->arquivo;
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

    public function uploadImagem($arquivos, $caminho, $dados){
        if(!file_exists($caminho)){
            mkdir($caminho);
        }
        

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                $nomeArquivoServer = 'arquivo'.date('dhsi');
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$nomeArquivoServer.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$nomeArquivoServer.'.'.$extensao;
                }
            }
        }

        return $dados;
    }


}

