<?php

namespace Avaliacaodiaria\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Avaliacaodiaria\Form\PesquisaAvaliacao as pesquisaForm;
use Avaliacaodiaria\Form\LiberarAvaliacao as liberarForm;
use Avaliacaodiaria\Form\Avaliacao as avaliacaoForm;
use Avaliacaodiaria\Form\Grafico as graficoForm;
use Avaliacaodiaria\Form\GraficoPersonalizado as graficoPersonalizadoForm;
use Avaliacaodiaria\Form\GraficoPersonalizadoEmpresa as graficoPersonalizadoEmpresaForm;
use Avaliacaodiaria\Form\GraficoPersonalizadoCampo as graficoPersonalizadoCampoForm;

use Zend\Session\Container;

class AvaliacaoController extends BaseController
{
    private $numeroLinhas = 0;

    public function indexAction()
    {
        $this->layout('layout/admindiaria');
        $formPesquisa = new pesquisaForm('formPesquisa', $this->getServiceLocator());

        $sessao = new Container();

        if(!isset($sessao->parametros)){
            $sessao->parametros = array('data_referencia' => date('d/m/Y'), 'respondidas' => 'N');
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                //redir para mesma página
                unset($sessao->parametros);
                return $this->redirect()->toRoute('administradorAvaliacoesDiarias');
            }else{
                //paràmetros da pesquisa
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $sessao->parametros = $formPesquisa->getData();
                }
            }
        }

        $respondida = $sessao->parametros['respondidas'];
        $formPesquisa->setData($sessao->parametros);
        $dados = $sessao->parametros;
        if(isset($sessao->parametros['data_referencia'])){
            $dados['data_referencia'] = $formPesquisa->converterData($dados['data_referencia']);
        }
        //buscar avaliacções
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoDiaria');
        
        if($respondida == 'N'){
            //pesquisar avaliações não respondidas
            $avaliacoes = $serviceAvaliacao->getAvaliacoesAbertas($dados);
        }else{
            //pesquisar avaliações respondidas
            $avaliacoes = $serviceAvaliacao->getAvaliacoesRespondidas($dados);
        }

 
        //paginar
        $paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);
        return new ViewModel(array(
        		'formPesquisa'		=> $formPesquisa,
        		'avaliacoes'		=> $paginator,
                'respondida'        => $respondida,
                'dataReferencia'    => $dados['data_referencia']
        	));
    }

    public function liberaravaliacoesAction(){
        $this->layout('layout/admindiaria');
        $formAvaliacao = new liberarform('formLiberarAvaliacao');
        $servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacaoDiaria');

        $dataAtual = date('Y-m-d');
        $formAvaliacao->setData(array('inicio' => $dataAtual));
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formAvaliacao->setData($dados);

            if($formAvaliacao->isValid()){
                    if($servicePilhaAvaliacoes->inserir($formAvaliacao->getData(), $this->definirDias())){
                        $this->flashMessenger()->addSuccessMessage('Período de avaliações inserido com sucesso!');

                    }else{
                        $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao liberar avaliações!');       
                    }
                
                return $this->redirect()->toRoute('liberarAvaliacoesDiarias');

            }
        }

        //avaliacoes liberadas
        $avaliacoesLiberadas = $servicePilhaAvaliacoes->getRecordsFromArray(array(), 'data_referencia DESC');

        //paginar
        $paginator = new Paginator(new ArrayAdapter($avaliacoesLiberadas->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new viewModel(array(
                'formAvaliacao'         => $formAvaliacao,
                'avaliacoesLiberadas'   => $paginator
            ));
    }

    public function deletarliberacaoAction(){
        $this->getServiceLocator()->get('PilhaAvaliacaoDiaria')->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Período de avaliação excluído com sucesso!');
        return $this->redirect()->toRoute('liberarAvaliacoesDiarias');
    }

    public function listaravaliacoesAction(){
        $this->layout('layout/admindiaria');
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        //pesquisar avaliações
        $servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacaoDiaria');

        $serviceAvaliacoes = $this->getServiceLocator()->get('AvaliacaoDiaria');
        //pesquisar empresa
        $operador = $this->getServiceLocator()->get('OperadorAvaliacaoDiaria')->getRecord($usuario['avaliacao_diaria']);

        $avaliacoes = $serviceAvaliacoes->getAvaliacoesRespondidas(array('empresa' => $operador->empresa));

        //paginar avaliacoes
        $Paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        //verificar se existem avaliações em aberto
        $avaliacoesAbertas = $serviceAvaliacoes->getAvaliacoesAbertasByOperador(array('empresa' => $operador->empresa));
        //$avaliacoesAbertas = $servicePilhaAvaliacoes->getAvaliacoesAbertas(date('Y-m-d'), $operador->empresa);
        $avaliacaoAberta = false;
        if($avaliacoesAbertas->count() > 0){
            $avaliacaoAberta = true;
        }

        return new ViewModel(array(
                'avaliacoes'        =>  $Paginator,
                'avaliacaoAberta'   =>  $avaliacaoAberta
            ));
    }

    public function avaliacaoAction(){
        $this->layout('layout/avaliacaodiaria');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $operadorDiaria = $this->getServiceLocator()->get('OperadorAvaliacaoDiaria')->getRecord($usuario['avaliacao_diaria']);
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(10, $operadorDiaria['empresa'])->toArray();

        //desabilitar campos
        $formAvaliacao = new AvaliacaoForm('formAvaliacao', $this->getServiceLocator(), $campos);
        $formAvaliacao->desabilitarCampos();

        if(!$formAvaliacao){
            return $this->redirect()->toRoute('listarAvaliacoesDiarias');
        }
        //se já passou das 11 da manhã não pode mais responder a avaliação
        /*if(date('H') >= 17){
            $this->flashMessenger()->addWarningMessage('Avaliações só podem ser respondidas até as 11 da manhã!');
            return $this->redirect()->toRoute('listarAvaliacoesDiarias');
        }*/

        if(!empty($this->params()->fromRoute('dataAvaliacao'))){
            $dataReferencia = $this->params()->fromRoute('dataAvaliacao');
        }else{
            $dataReferencia = date('Y-m-d');
        }

       
        //verificar se não for fim de semana ou feriado subtrai um dia
        $subtrairDias = true;
        if($this->definirDias()->diaNaoUtil($dataReferencia)){
            $subtrairDias = false;
        }

        $serviceDiaria = $this->getServiceLocator()->get('AvaliacaoDiaria');
        $avaliacoesNaoRespondidas = $serviceDiaria->getAvaliacoesAbertasByOperador(array('empresa' => $operadorDiaria->empresa));

        if($this->getRequest()->isPost()){
            $formAvaliacao->setData($this->getRequest()->getPost());
            $dados = $this->getRequest()->getPost();

            //remover os campos calculados
            $formAvaliacao->removerCampos();

            if($formAvaliacao->isValid()){
                $dados = $formAvaliacao->getData();
                $dados['empresa']         = $operadorDiaria->empresa;
                $dados['usuario']         = $usuario['id'];
                $dados['data_referencia'] = $dataReferencia;
                
                $idAvaliacao = $serviceDiaria->insert($dados);
                $this->flashMessenger()->addSuccessMessage('Avaliação inserida com sucesso!');

                if($avaliacoesNaoRespondidas->count() > 1){
                    return $this->redirect()->toRoute('avaliacaoDiaria');
                }else{
                    return $this->redirect()->toRoute('listarAvaliacoesDiarias');
                }
            }
        }

        return new ViewModel(array(
                'formAvaliacao'             => $formAvaliacao,
                'campos'                    => $campos,
                'avaliacoesNaoRespondidas'  => $avaliacoesNaoRespondidas,
                'dataReferencia'            => $dataReferencia
            ));

    }

    public function visualizaravaliacaoAction(){
        $this->layout('layout/avaliacaodiaria');
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');

        //pesquisar avaliação
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoDiaria');
        $avaliacao = $serviceAvaliacao->getAvaliacacoById($idAvaliacao);
        if(!$avaliacao){
            $this->flashMessenger()->addWarningMessage('Nenhuma avaliação encontrada!');
            return $this->redirect()->toRoute('listarAvaliacoesDiarias');
        }
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(10, $avaliacao->empresa)->toArray();
        $formAvaliacao = new AvaliacaoForm('formAvaliacao', $this->getServiceLocator(), $campos);
        
        $formAvaliacao->setData($avaliacao);
        //desabilitar campos da avaliação
        foreach ($formAvaliacao as $field) {
            $formAvaliacao->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
        }

        $formAvaliacaoOriginal = false;
        $avaliacaoOriginal = false;
        if($avaliacao['auditado'] == 'I'){
            //verificar se existe avaliação com respondido N
            $avaliacaoOriginal = $serviceAvaliacao->getRecordFromArray(array(
                    'empresa'           => $avaliacao->empresa,
                    'data_referencia'   => $avaliacao->data_referencia,
                    'auditado'          => 'S'
                ));

            if($avaliacaoOriginal){
                $formAvaliacaoOriginal = new AvaliacaoForm('formAvaliacaoOriginal', $this->getServiceLocator(), $campos);
                $formAvaliacaoOriginal->setData($avaliacaoOriginal);
                foreach ($formAvaliacaoOriginal as $field) {
                    $formAvaliacaoOriginal->get($field->getName())->setAttributes(array('disabled' => 'disabled'));
                }
            }
        }

        $usuario = $this->getServiceLocator()->get('session')->read();
        return new ViewModel(array(
                'formAvaliacao'             =>  $formAvaliacao,
                'avaliacao'                 =>  $avaliacao,
                'formAvaliacaoOriginal'     =>  $formAvaliacaoOriginal,
                'avaliacaoOriginal'         =>  $avaliacaoOriginal,
                'usuario'                   =>  $usuario,
                'campos'                    =>  $campos
            ));
    }

    public function graficoAction(){
        $this->layout('layout/admindiaria');

        //gerar form de pesquisa
        $formGrafico = new graficoForm('formGrafico', $this->getServiceLocator());
        
        return new ViewModel(array(
                'formGrafico'       =>  $formGrafico
            ));
    }

    public function visualizargraficoAction(){
        $this->layout('layout/admindiaria');
        $formGrafico = new graficoForm('formGrafico', $this->getServiceLocator());
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            //se for do tipo excell redirencionar para interface de relatório
            if($dados['tipoRelatiorio'] == 2 || $dados['tipoRelatiorio'] == 3){
                //excel layout 1
                $sessao = new Container();
                $sessao->offsetSet('inicio', $formGrafico->converterData($dados['inicio']));
                $sessao->offsetSet('fim', $formGrafico->converterData($dados['fim']));
                $sessao->offsetSet('empresa', $dados['empresa']);
                return $this->redirect()->toRoute('planilhaAvaliacaoDiaria');
            }else{
                if($dados['tipoRelatiorio'] == 3){
                    //excel layout 2
                    $sessao = new Container();
                    $sessao->offsetSet('inicio', $formGrafico->converterData($dados['inicio']));
                    $sessao->offsetSet('fim', $formGrafico->converterData($dados['fim']));
                    $sessao->offsetSet('empresa', $dados['empresa']);
                    return $this->redirect()->toRoute('planilhaAvaliacaoDiaria2');
                }else{
                    if($dados['tipoRelatiorio'] == 4){
                        //relatório de banco de dados
                        $sessao = new Container();
                        $sessao->offsetSet('inicio', $formGrafico->converterData($dados['inicio']));
                        $sessao->offsetSet('fim', $formGrafico->converterData($dados['fim']));
                        $sessao->offsetSet('empresa', $dados['empresa']);
                        return $this->redirect()->toRoute('planilhaBancoDadosAvaliacaoDiaria');  
                    }
                }
            }
        }else{
            //pegar url params
            $data = $formGrafico->converterData($this->params()->fromRoute('data'));
            $dados = array(
                    'empresa' => $this->params()->fromRoute('empresa'),
                    'inicio'  => $data,
                    'fim'     => $data
                );
        }
        
        $formGrafico->setData($dados);
        if($formGrafico->isValid()){
            $dados = $formGrafico->getData();
            $avaliacoes = $this->getServiceLocator()->get('AvaliacaoDiaria')->getAvaliacoesByParams($dados);
            
            //pesquisar campos
            $somaAvaliacoes = $this->somarCamposPlanilha($avaliacoes);
        }

        if(!$avaliacoes || $avaliacoes->count() < 1){
            $this->flashMessenger()->addWarningMessage('Nenhuma avaliação encontrada!');
            return $this->redirect()->toRoute('administradorAvaliacoesDiarias');
        }

        //se for adm ele precisa tirar alguns itens do breadcrumb
        $usuario = $this->getServiceLocator()->get('session')->read();
        return new ViewModel(array(
            'somaAvaliacoes'    => $somaAvaliacoes,
            'usuario'           => $usuario
        ));
    }

    private function somarCamposPlanilha($avaliacoes){
        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(10)->toArray();
        $somaAvaliacoes = array();
        
        foreach ($campos as $campo) {
            $somaAvaliacoes[$campo['nome_campo']]['valor'] = 0;
            $somaAvaliacoes[$campo['nome_campo']]['label'] = $campo['label'];
        }

        foreach ($avaliacoes as $avaliacao) {
            foreach ($campos as $campo) {
                //if($campo['categoria_questao'] != 32){
                    $somaAvaliacoes[$campo['nome_campo']]['valor'] += $avaliacao[$campo['nome_campo']];
                //}
            }
        }
        return $somaAvaliacoes;
    }

    public function graficopersonalizadoAction(){
        //verificar se veve loimpar os parametros
        $serviceSubGrafico = $this->getServiceLocator()->get('AvaliacaoDiariaGraficoSub');
        $limpar = $this->params()->fromRoute('limpar');
        if($limpar == 'S'){
            $serviceSubGrafico->delete(array(1 => 1));
            $this->flashMessenger()->addSuccessMessage('Parâmetros excluídos com sucesso!');
            return $this->redirect()->toRoute('graficoPersonalizadoAvaliacoesDiarias');
        }

        $this->layout('layout/admindiaria');
        $formGrafico = new graficoPersonalizadoForm('formGrafico');

        //pesquisar se existe alguma data registrada, caso sim abrir subform
        $serviceDatasGrafico = $this->getServiceLocator()->get('AvaliacaoDiariaGrafico');
        $datasPesquisa = $serviceDatasGrafico->fetchAll();
        $formEmpresas = false;
        $formCampos = false;
        if($datasPesquisa->count() > 0){
            $formGrafico->setData($datasPesquisa->current());
            
            $formEmpresas = new graficoPersonalizadoEmpresaForm('formEmpresa', $this->getServiceLocator());
            $formCampos = new graficoPersonalizadoCampoForm('formCampos', $this->getServiceLocator());
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->inicio)){
                //salvar datas
                $formGrafico->setData($dados);
                if($formGrafico->isValid()){
                    $serviceDatasGrafico->update($formGrafico->getData(), array('id' => 1));
                    $this->flashMessenger()->addSuccessMessage('Data de pesquisa alterada com sucesso!');
                    return $this->redirect()->toRoute('graficoPersonalizadoAvaliacoesDiarias');
                }
            }else{
                if(isset($dados->empresa)){
                    //salvar empresa
                    if($dados['empresa'] == 'T'){
                        $empresas = $this->getServiceLocator()->get('Empresa')
                                        ->getRecordsFromArray(
                                            array('ativo' => 'S', 'avaliacao_diaria' => 'S'), 
                                            'nome_empresa', 
                                            array('id', 'nome_empresa')
                                        );

                        if($serviceSubGrafico->inserir($empresas)){
                            $this->flashMessenger()->addSuccessMessage('Empresas vinculadas com sucesso!');
                        }else{
                            $this->flashMessenger()->addSuccessMessage('Ocorreu algum erro ao inserir empresas!');
                        }
                    }else{
                        $serviceSubGrafico->insert(array('empresa' => $dados->empresa));
                        $this->flashMessenger()->addSuccessMessage('Empresa vinculada com sucesso!');
                    }
                    return $this->redirect()->toRoute('graficoPersonalizadoAvaliacoesDiarias');
                }else{
                    //salvar campo do formulário que deve ser exibido
                    $serviceSubGrafico->insert(array('campo' => $dados->campo));
                    $this->flashMessenger()->addSuccessMessage('Campo vinculado com sucesso!');
                    return $this->redirect()->toRoute('graficoPersonalizadoAvaliacoesDiarias');
                }
            }
        }

        //pesquisar empresas vinculadas
        $empresas = $serviceSubGrafico->getEmpresas();
        $campos = $serviceSubGrafico->getCampos();

        return new ViewModel(array(
                'formGrafico'       =>  $formGrafico,
                'formEmpresas'      =>  $formEmpresas,
                'formCampos'        =>  $formCampos,
                'empresas'          =>  $empresas,
                'campos'            =>  $campos
            ));

    }

    public function visualizargraficopersonalizadoAction(){
        $this->layout('layout/admindiaria');
        $serviceparametros = $this->getServiceLocator()->get('AvaliacaoDiariaGraficoSub');

        //pesquisar datas 
        $datas = $this->getServiceLocator()->get('AvaliacaoDiariaGrafico')->getRecord(1);

        //pesquisar empresas
        $empresas = $serviceparametros->getEmpresas()->toArray();
        if(count($empresas) == 0){
            $this->flashMessenger()->addWarningMessage('Nenhuma empresa selecionada!');
            return $this->redirect()->toRoute('graficoPersonalizadoAvaliacoesDiarias');
        }
        $idEmpresas = array();
        foreach ($empresas as $empresa) {
            $idEmpresas[] = $empresa['id_empresa'];
        }
        
        //pesquisar campos
        $campos = $serviceparametros->getCampos()->toArray();
        if(count($campos) == 0){
            $this->flashMessenger()->addWarningMessage('Nenhum campo selecionado!');
            return $this->redirect()->toRoute('graficoPersonalizadoAvaliacoesDiarias');
        }
        $nomesCampos = array();
        foreach ($campos as $campo) {
            $nomesCampos[] = $campo['nome_campo'];
        }

        //pesquisar avaliação 
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoDiaria');
        $avaliacoes = $serviceAvaliacao->getAvaliacoesGraficoPersonalizado($idEmpresas, $nomesCampos, $datas)->toArray();
        $avaliacoes = $this->prepararDados($avaliacoes, $campos);
        
        
        return new ViewModel(array(
            'avaliacoes' => $avaliacoes,
            'campos'     => $campos,
            'empresas'   => $empresas
            ));
    }

    private function prepararDados($avaliacoes, $campos){
        //somar os campos das mesmas empresas
        $somaAvaliacoes = array();
        $empresa = 0;
        foreach ($avaliacoes as $avaliacao) {
            if($avaliacao['id_empresa'] != $empresa){
                $somaAvaliacoes[$avaliacao['id_empresa']] = $this->iniciarVetor($campos, $avaliacao);
                $empresa = $avaliacao['id_empresa'];
            }

            $somaAvaliacoes[$avaliacao['id_empresa']] = $this->somarCampos($campos, $avaliacao, $somaAvaliacoes[$avaliacao['id_empresa']]);
        }
        
        return $somaAvaliacoes;
    }

    private function somarCampos($campos, $avaliacao, $somaAvaliacoes){
        foreach ($campos as $campo) {
            $somaAvaliacoes[$campo['nome_campo']]['valor'] += $avaliacao[$campo['nome_campo']];
        }
        return $somaAvaliacoes;
    }

    private function iniciarVetor($campos = false, $avaliacao = false){
        $somaAvaliacoes = array();
        if($campos){
            foreach ($campos as $campo) {
                $somaAvaliacoes['nome_empresa'] = $avaliacao['nome_empresa'];
                $somaAvaliacoes[$campo['nome_campo']]['label'] = $campo['label'];
                $somaAvaliacoes[$campo['nome_campo']]['valor'] = 0;
            }
        }
        return $somaAvaliacoes;
    }

    public function planilhaavaliacaodiariaAction(){
        $sessao = new Container();
        $formPesquisa = new graficoPersonalizadoForm('formPesquisa');
        $dados = array(
                'inicio'    =>  $sessao->offsetGet('inicio'),
                'fim'       =>  $sessao->offsetGet('fim'),
                'empresa'   =>  $sessao->offsetGet('empresa')
            );


        //pesquisar avaliações por período, se vier empresa, pesquisar também por empresa
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoDiaria')->getAvaliacoesToExcell($dados)->toArray();
        
        //gerar relatório
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações diárias");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        $objPHPExcel->setActiveSheetIndex(0);
        $this->numeroLinhas = 1;
        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(10)->toArray();
        $objPHPExcel = $this->escreverCabecalhoExcel($objPHPExcel);

        //somar valores da mesma empresa
        $avaliacoes = $this->somarCamposByEmpresa($avaliacoes, $campos);

        foreach ($avaliacoes as $avaliacao) {
                $objPHPExcel = $this->escreverColunaRelatorio($objPHPExcel, $avaliacao, $campos);
        }

        $objPHPExcel->getActiveSheet()->getStyle('A1:U'.$this->numeroLinhas)
                ->getAlignment()->setWrapText(true); 


        //inserir imagem
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $diretorio = 'public/relatorios/diario';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/'.date('d-m-Y').'-diario.xlsx';
        $objWriter->save($fileName);
        
        //salvar caminho do arquivo na sessão
        $sessao = new Container();
        $sessao->offsetSet('xlsx', $fileName);

        //enviar para download
        return $this->redirect()->toRoute('downloadPlanilha');
    }

    public function planilhaavaliacaodiaria2Action(){
        $sessao = new Container();
        $formPesquisa = new graficoPersonalizadoForm('formPesquisa');
        $dados = array(
                'inicio'    =>  $sessao->offsetGet('inicio'),
                'fim'       =>  $sessao->offsetGet('fim'),
                'empresa'   =>  $sessao->offsetGet('empresa')
            );

        if(empty($dados['empresa'])){
            $this->flashMessenger()->addWarningMessage('Selecione uma clínica para a planílha individual!');
            return $this->redirect()->toRoute('graficoAvaliacoesDiarias');
        }

        //pesquisar avaliações por período, se vier empresa, pesquisar também por empresa
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoDiaria')->getAvaliacoesToExcell($dados)->toArray();

        //gerar relatório
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações diárias");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        
        $objPHPExcel->getActiveSheet()->mergeCells('A1:R6');
        $this->numeroLinhas = 7;

        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(10)->toArray();
        $objPHPExcel = $this->escreverCabecalhoExcelDiario($objPHPExcel, $avaliacoes[0]['nome_empresa'], $campos);

        //dados das empresas
        $numeroLetra = 2;
        $arrayLetras = $this->alfabeto();

        foreach ($avaliacoes as $avaliacao) {
                $letra = $arrayLetras[$numeroLetra];
                $objPHPExcel = $this->escreverColunaRelatorioDiario($objPHPExcel, $letra, $avaliacao, $campos);
                $numeroLetra++;
        }
        

        foreach(range('A','Z') as $columnID) {
            //COLUNAS SE AJUSTAM AUTOMATICAMENTE AO TEXTO
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        
        //inserir imagem
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(70);
        $gdImage = imagecreatefrompng('public/img/alliar.png');
        $objDrawing = new \PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Sample image');
        $objDrawing->setDescription('Sample image');
        $objDrawing->setImageResource($gdImage);
        $objDrawing->setRenderingFunction(\PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
        $objDrawing->setMimeType(\PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setHeight(120);
        $objDrawing->setWidth(300);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        
        $diretorio = 'public/relatorios/diario';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/'.date('d-m-Y').'-diario.xlsx';
        $objWriter->save($fileName);
        
        //salvar caminho do arquivo na sessão
        $sessao = new Container();
        $sessao->offsetSet('xlsx', $fileName);

        //enviar para download
        return $this->redirect()->toRoute('downloadPlanilha');
    }

    private function somarCamposByEmpresa($avaliacoes, $campos){
        $empresa = 0;
        $somaAvaliacoes = array();
        $keySoma = -1;
        $contMedia = 0;
        foreach ($avaliacoes as $key => $avaliacao) {
            if($avaliacao['id_empresa'] != $empresa){
                $empresa = $avaliacao['id_empresa'];
                $keySoma++;
                $somaAvaliacoes[$keySoma] = $avaliacao;
                $contMedia = 0;
            }
            $proximaKey = $key+1;
            $contMedia++;
            if(array_key_exists($proximaKey, $avaliacoes) && $avaliacoes[$proximaKey]['id_empresa'] == $empresa){
                foreach ($campos as $campo) {
                    if($campo['nome_campo'] == 'tme' 
                        || $campo['nome_campo'] == 'tma' 
                        || $campo['nome_campo'] == 'tempo_logado')
                    {   
                        $somaAvaliacoes[$keySoma][$campo['nome_campo']] = 
                            $this->definirDias()->somarHoras($somaAvaliacoes[$keySoma][$campo['nome_campo']],
                            $avaliacoes[$proximaKey][$campo['nome_campo']]);
                    }else{
                        $somaAvaliacoes[$keySoma][$campo['nome_campo']] += $avaliacoes[$proximaKey][$campo['nome_campo']];
                    }
                }
            }
        }
        //gerar médias de % da última coluna
        $somaAvaliacoes = $this->somaPorcentagem($somaAvaliacoes, $keySoma, $contMedia);

        return $somaAvaliacoes;
    }

    private function somaPorcentagem($avaliacoes, $key, $numeroMedia){
        $avaliacoes[$key]['atraso'] = number_format($avaliacoes[$key]['atraso']/$numeroMedia,2).'%';
        return $avaliacoes;
    }

    private function escreverCabecalhoExcel($objPHPExcel){
        //FONTE EM BRANCO
        $objPHPExcel->getActiveSheet()->getStyle("E1:U3")->applyFromArray(array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 10,
            )
        ));
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1');

        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Empresa');        

        $objPHPExcel->getActiveSheet()->mergeCells('B2:D2');
        $objPHPExcel->getActiveSheet()->SetCellValue('B3', 'Responsável'); 
        $objPHPExcel->getActiveSheet()->SetCellValue('C3', 'Andar');
        $objPHPExcel->getActiveSheet()->SetCellValue('D3', 'Data');

        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'TME 1 (minutos)');
        $objPHPExcel->getActiveSheet()->mergeCells('E2:E3');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'Recepção');

        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'TMA recepção (minutos)');
        $objPHPExcel->getActiveSheet()->mergeCells('F2:F3');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'Recepção');

        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Atraso (% atendidos em até 15 min)');
        $objPHPExcel->getActiveSheet()->mergeCells('G2:G3');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'Área téc');

        
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Total de atendimentos');
        $objPHPExcel->getActiveSheet()->mergeCells('H2:H3');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'Recepção');

        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Quantidade de entregas via motoboy');
        $objPHPExcel->getActiveSheet()->mergeCells('I2:I3');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'Recepção');

        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Quantidade de atendimentos sem pendência/enviado para faturamento');
        $objPHPExcel->getActiveSheet()->mergeCells('J2:J3');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'Recepção');

        /*$objPHPExcel->getActiveSheet()->mergeCells('J1:U1');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Quantidade de funcionários');

        $objPHPExcel->getActiveSheet()->mergeCells('J2:M2');
        $objPHPExcel->getActiveSheet()->SetCellValue('N2', 'Recepção');
        $objPHPExcel->getActiveSheet()->SetCellValue('J3', 'Folha');
        $objPHPExcel->getActiveSheet()->SetCellValue('K3', 'Faltas');
        $objPHPExcel->getActiveSheet()->SetCellValue('L3', 'Férias');
        $objPHPExcel->getActiveSheet()->SetCellValue('M3', 'Líquido');

        $objPHPExcel->getActiveSheet()->mergeCells('N2:Q2');
        $objPHPExcel->getActiveSheet()->SetCellValue('N2', 'Enfermagem');
        $objPHPExcel->getActiveSheet()->SetCellValue('N3', 'Folha');
        $objPHPExcel->getActiveSheet()->SetCellValue('O3', 'Faltas');
        $objPHPExcel->getActiveSheet()->SetCellValue('P3', 'Férias');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q3', 'Líquido');

        $objPHPExcel->getActiveSheet()->mergeCells('R2:U2');
        $objPHPExcel->getActiveSheet()->SetCellValue('R2', 'Téc e biomédicos');
        $objPHPExcel->getActiveSheet()->SetCellValue('R3', 'Folha');
        $objPHPExcel->getActiveSheet()->SetCellValue('S3', 'Faltas');
        $objPHPExcel->getActiveSheet()->SetCellValue('T3', 'Férias');
        $objPHPExcel->getActiveSheet()->SetCellValue('U3', 'Líquido');*/


        $objPHPExcel->getActiveSheet()->getStyle("B2:D3")->applyFromArray(array(
            'font'  => array(
                'bold'  => true,
            )
        ));
        
        //AZUL
        $objPHPExcel->getActiveSheet()->getStyle("E1:J3")->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                 'rgb' => '0000FF'
            )
        ));

        //PRETO
        /*$objPHPExcel->getActiveSheet()->getStyle("J1:U3")->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                 'rgb' => '000000'
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("M3:M3")->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                 'rgb' => '#000000'
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("Q3:Q3")->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                 'rgb' => '#000000'
            )
        ));

        $objPHPExcel->getActiveSheet()->getStyle("U3:U3")->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                 'rgb' => '#000000'
            )
        ));*/


       $this->numeroLinhas = 4;

        return $objPHPExcel;
    }

    private function escreverColunaRelatorio($objPHPExcel, $avaliacao, $campos){
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, $avaliacao['nome_empresa']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$this->numeroLinhas, $avaliacao['nome_responsavel']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$this->numeroLinhas, 'N/A');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$this->numeroLinhas, $this->funcoes()->converterData($avaliacao['data_hora_resposta']));
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$this->numeroLinhas, $avaliacao['tme']);
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$this->numeroLinhas, $avaliacao['tma']);
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$this->numeroLinhas, $avaliacao['atraso']);
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$this->numeroLinhas, $avaliacao['total_atendimentos']);
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$this->numeroLinhas, $avaliacao['entrega_motoboy']);
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$this->numeroLinhas, $avaliacao['sem_pendencia']);
        /*$objPHPExcel->getActiveSheet()->SetCellValue('J'.$this->numeroLinhas, $avaliacao['recepcao_folha']);
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$this->numeroLinhas, $avaliacao['recepcao_faltas']);
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$this->numeroLinhas, $avaliacao['recepcao_ferias']);
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$this->numeroLinhas, $avaliacao['recepcao_liquido']);
        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$this->numeroLinhas, $avaliacao['enfermagem_folha']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$this->numeroLinhas, $avaliacao['enfermagem_faltas']);
        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$this->numeroLinhas, $avaliacao['enfermagem_ferias']);
        $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$this->numeroLinhas, $avaliacao['enfermagem_liquido']);
        $objPHPExcel->getActiveSheet()->SetCellValue('R'.$this->numeroLinhas, $avaliacao['biomedicos_folha']);
        $objPHPExcel->getActiveSheet()->SetCellValue('S'.$this->numeroLinhas, $avaliacao['biomedicos_faltas']);
        $objPHPExcel->getActiveSheet()->SetCellValue('T'.$this->numeroLinhas, $avaliacao['biomedicos_ferias']);
        $objPHPExcel->getActiveSheet()->SetCellValue('U'.$this->numeroLinhas, $avaliacao['biomedicos_liquido']);*/

        
        $this->numeroLinhas++;

        return $objPHPExcel;
    }

    private function escreverCabecalhoExcelDiario($objPHPExcel, $empresa, $campos){
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, 'INDICADORES AVALIAÇÃO DIÁRIA - '.$empresa);
        //cinza claro
        $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:R$this->numeroLinhas")->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                 'rgb' => 'CFCFCF'
            ),
        ));

        $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:R$this->numeroLinhas")->applyFromArray(array(
                'font'  => array(
                    'bold'  => true,
                )
            ));

        $arrayCinzaEscuro = array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                     'rgb' => 'B5B5B5'
                )
            );


        $categoriaQuestao = 0;
        foreach ($campos as $campo) {
            if($categoriaQuestao != $campo['categoria_questao']){
                $categoriaQuestao = $campo['categoria_questao'];
                $this->numeroLinhas++;
                
                $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:A$this->numeroLinhas")->applyFromArray(array(
                    'font'  => array(
                        'bold'  => true,
                    )
                ));
                $nomeCategoria = strtr(strtoupper($campo['nome_categoria']),"àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ","ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß");
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, $nomeCategoria);

                $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:R$this->numeroLinhas")->getFill()->applyFromArray($arrayCinzaEscuro);
            }
            $this->numeroLinhas++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, $campo['sinonimo_label']);

            //formatar linha
            $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:R$this->numeroLinhas")->applyFromArray(array(
                    'font'  => array(
                        'size'  => 10,
                    )
                ));
        }

        $this->numeroLinhas++;

        return $objPHPExcel;
    }

    private function escreverColunaRelatorioDiario($objPHPExcel, $letra, $avaliacao, $campos){
        $pluginFuncoes = $this->funcoes();

        $linha = 7;
        $objPHPExcel->getActiveSheet()->getStyle($letra.$linha.':'.$letra.'44')->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        
        $objPHPExcel->getActiveSheet()->SetCellValue($letra.$linha, $pluginFuncoes->diaToSemana($avaliacao['data_referencia']));
        $linha++;
        
        $data = explode('-', $avaliacao['data_referencia']);
        $objPHPExcel->getActiveSheet()->SetCellValue($letra.$linha, $data[2].'/'.$pluginFuncoes->numeroToMes($data[1]));

        $categoriaQuestao = 0;
        foreach ($campos as $campo) {
            if($categoriaQuestao != $campo['categoria_questao']){
                $linha++;
                $categoriaQuestao = $campo['categoria_questao'];
            }
            $objPHPExcel->getActiveSheet()->SetCellValue($letra.$linha, $avaliacao[$campo['nome_campo']]);
            $linha++;
        }

        return $objPHPExcel;
    }


    public function listaavaliacoesadmAction(){

        $formPesquisa = new pesquisaForm('formPesquisa', $this->getServiceLocator());

        $parametros = array('data_referencia' => date('d/m/Y'), 'respondidas' => 'N');
        if($this->getRequest()->isPost()){
            $formPesquisa->setData($this->getRequest()->getPost());
        }else{
            $formPesquisa->setData($parametros);
        }
        if($formPesquisa->isValid()){
            $parametros = $formPesquisa->getData();
            $respondida = $parametros['respondidas'];
        }


        //buscar avaliacções
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoDiaria');
        
        if($respondida == 'N'){
            //pesquisar avaliações não respondidas
            $avaliacoes = $serviceAvaliacao->getAvaliacoesAbertas($parametros);
        }else{
            //pesquisar avaliações respondidas
            $avaliacoes = $serviceAvaliacao->getAvaliacoesRespondidas($parametros);
        }

        //paginar
        $paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                'formPesquisa'      => $formPesquisa,
                'avaliacoes'        => $paginator,
                'respondida'        => $respondida,
                'dataReferencia'    => $parametros['data_referencia']
            ));
    }

        public function planilhabancodadosAction(){
        $formPesquisa = new pesquisaForm('formPesquisa', $this->getServiceLocator());
        $sessao = new Container();
        $dados = array(
                'inicio'    =>  $sessao->offsetGet('inicio'),
                'fim'       =>  $sessao->offsetGet('fim'),
                'empresa'   =>  $sessao->offsetGet('empresa')
            );
        
        //Início do excel
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações diárias");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);

        //gerar cabeçalho
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Unidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Usuário');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Data de referência');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Categoria');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Indicador');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Resultado');

        //pesquisar campos
        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(10)->toArray();
        
        //pesquisar avaliações para o período
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoDiaria')->getAvaliacoesToExcell2($dados)->toArray();

        //percorrer avaliações
        $numeroLinhas = 2;
        foreach ($avaliacoes as $avaliacao) {
            foreach ($campos as $campo) {
                //percorrer campos e inserir no relatório
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numeroLinhas, $avaliacao['nome_empresa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numeroLinhas, $avaliacao['nome_usuario']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numeroLinhas, $formPesquisa->converterData($avaliacao['data_referencia']));
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numeroLinhas, $campo['nome_categoria']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numeroLinhas, $campo['label']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$numeroLinhas, $avaliacao[$campo['nome_campo']]);
                $numeroLinhas++;
            }
        }

                
        // Save Excel 2007 file
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        
        $diretorio = 'public/relatorios';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/relatorioBancoDiaria.xlsx';
        $objWriter->save($fileName);
        
        //salvar caminho do arquivo na sessão
        $sessao = new Container();
        $sessao->offsetSet('xlsx', $fileName);

        //enviar para download
        return $this->redirect()->toRoute('downloadPlanilha');

    }

    public function novaavaliacaoadmAction(){
        $this->layout('layout/avaliacaodiaria');

        //pegar dados da url
        $empresa = $this->params()->fromRoute('empresa');
        $dataReferencia = $this->params()->fromRoute('data');
        $vetorDataReferencia = explode('-', $dataReferencia);

        //validar url params
        if(empty($empresa) || empty($dataReferencia)){
            $this->flashMessenger()->addWarningMessage('Parâmetros inválidos!');
            return $this->redirect()->toRoute('administradorAvaliacoesDiarias');
        }

        //pesquisar avaliação
        $serviceDiaria = $this->getServiceLocator()->get('AvaliacaoDiaria');
        $avaliacao = $serviceDiaria->getRecordFromArray(array('empresa' => $empresa, 'data_referencia' => $dataReferencia));

        //se existe redirecionar para alterar avaliacao
        if($avaliacao){
            $this->flashMessenger()->addInfoMessage('Avaliação já existe no sistema!');
            $this->redirect()->toRoute('alterarAvaliacaoDiariaAdm', array('id' => $avaliacao['id']));
        }

        //se não existe instanciar form
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(10, $empresa)->toArray();

        //desabilitar campos
        $formAvaliacao = new AvaliacaoForm('formAvaliacao', $this->getServiceLocator(), $campos);
        $formAvaliacao->desabilitarCampos();

        //se post salvar dados
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formAvaliacao->setData($dados);

            //remover os campos calculados
            $formAvaliacao->removerCampos();

            if($formAvaliacao->isValid()){
                $dados = $formAvaliacao->getData();
                $usuario = $this->getServiceLocator()->get('session')->read();
                $dados['empresa']         = $empresa;
                $dados['usuario']         = $usuario['id'];
                $dados['data_referencia'] = $dataReferencia;
                $dados['auditado'] = 'I';

                $idAvaliacao = $this->getServiceLocator()->get('AvaliacaoDiaria')->insert($dados);
                $this->flashMessenger()->addSuccessMessage('Avaliação inserida com sucesso!');

                return $this->redirect()->toRoute('administradorAvaliacoesDiarias');
            }
        }

        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($empresa);

        //pesquisar avaliaçãoes anteriores não respondidas
        $avaliacoesNaoRespondidas = $serviceDiaria->getAvaliacoesAbertasByOperador(array('empresa' => $empresa->id));

        return new ViewModel(array(
                'formAvaliacao'            => $formAvaliacao,
                'dataReferencia'           => $dataReferencia,
                'campos'                   => $campos,
                'empresa'                  => $empresa,
                'avaliacoesNaoRespondidas' => $avaliacoesNaoRespondidas
            ));


    }

    public function alteraravaliacaoadmAction(){
        $this->layout('layout/avaliacaodiaria');
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoDiaria');

        //pesquisar avaliação por id
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');
        $avaliacao = $serviceAvaliacao->getRecord($idAvaliacao);
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(10, $avaliacao['empresa'])->toArray();
        $formAvaliacao = new AvaliacaoForm('formAvaliacao', $this->getServiceLocator(), $campos);
        $formAvaliacao->setData($avaliacao);
        $formAvaliacao->desabilitarCampos();

        //caso venha um post salvar avaliação
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formAvaliacao->setData($dados);

            $mes = explode('-', $avaliacao['data_referencia']);
            $mes = $mes[1];

            //remover os campos calculados
            if($formAvaliacao->isValid()){
                $dados = $formAvaliacao->getData();
                $usuario = $this->getServiceLocator()->get('session')->read();
                $dados['empresa']         = $avaliacao['empresa'];
                $dados['usuario']         = $usuario['id'];
                $dados['data_referencia'] = $avaliacao['data_referencia'];
                $dados['auditado'] = 'I';

                if($avaliacao['auditado'] == 'I'){
                    //update
                    $serviceAvaliacao->update($dados, array('id' => $idAvaliacao));
                    $this->flashMessenger()->addSuccessMessage('Avaliação alterada sucesso!');
                }else{
                    if($serviceAvaliacao->criarAvaliacaoAuditada($dados, $avaliacao['id'])){
                        $this->flashMessenger()->addSuccessMessage('Avaliação inserida com sucesso!');
                    }else{
                        $this->flashMessenger()->addWarningMessage('Ocorreu algum erro ao inserir avaliação, por favor tente novamente!');
                    }
                }

                return $this->redirect()->toRoute('administradorAvaliacoesDiarias');
            }
        }
        
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($avaliacao['empresa']);
        return new ViewModel(array(
                'formAvaliacao'     => $formAvaliacao,
                'dataReferencia'    => $avaliacao['data_referencia'],
                'campos'            => $campos,
                'empresa'           => $empresa

            ));
    }

}