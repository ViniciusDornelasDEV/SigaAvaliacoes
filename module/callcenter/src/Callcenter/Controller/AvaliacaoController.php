<?php

namespace Callcenter\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Callcenter\Form\PesquisaAvaliacao as pesquisaForm;
use Callcenter\Form\LiberarAvaliacao as liberarForm;
use Callcenter\Form\Avaliacao as avaliacaoForm;
use Callcenter\Form\Grafico as graficoForm;
use Callcenter\Form\GraficoPersonalizado as graficoPersonalizadoForm;
use Callcenter\Form\GraficoPersonalizadoEmpresa as graficoPersonalizadoEmpresaForm;
use Callcenter\Form\GraficoPersonalizadoCampo as graficoPersonalizadoCampoForm;
use Callcenter\Form\AlterarLiberacao as alterarLiberacaoForm;
use Zend\Session\Container;

class AvaliacaoController extends BaseController
{
    private $numeroLinhas = 0,
            $meta = false,
            $metaMensal = false,
            $metaMensalRestante = false,
            $diasRestantes = false,
            $metaDiasRestam = false;

    public function indexAction()
    {
        $this->layout('layout/admincallcenter');
        $formPesquisa = new pesquisaForm('formPesquisa', $this->getServiceLocator());

        $sessao = new Container();

        if(!isset($sessao->parametros)){
            $sessao->parametros = array('data_referencia' => date('Y-m-d'), 'respondidas' => 'N');
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->limpar)){
                //redir para mesma página
                unset($sessao->parametros);
                return $this->redirect()->toRoute('administradorAvaliacoesCallCenter');
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

        //buscar avaliacções
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoCallCenter');
        
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
        $this->layout('layout/admincallcenter');
        $formAvaliacao = new liberarform('formLiberarAvaliacao');
        $servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacaoCallCenter');

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
                
                return $this->redirect()->toRoute('liberarAvaliacoesCallCenter');

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

    public function alteraravaliacoesAction(){
        $form = new alterarLiberacaoForm('formAlterar');

        if($this->getRequest()->isPost()){
            $servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacaoCallCenter');
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()){
                $servicePilhaAvaliacoes->alterarDatas($form->getData());
                $this->flashMessenger()->addSuccessMessage('Datas de avaliação alteradas com sucesso!');
                return $this->redirect()->toRoute('liberarAvaliacoesCallCenter');
            }
        }
        return new ViewModel(array(
                'formAlterarAvaliacao'  =>  $form
            ));
    }

    public function deletarliberacaoAction(){
        $this->getServiceLocator()->get('PilhaAvaliacaoCallCenter')->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Período de avaliação excluído com sucesso!');
        return $this->redirect()->toRoute('liberarAvaliacoesCallCenter');
    }

    public function listaravaliacoesAction(){
        $this->layout('layout/admincallcenter');
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        //pesquisar avaliações
        $servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacaoCallCenter');

        $serviceAvaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter');
        //pesquisar empresa
        $operador = $this->getServiceLocator()->get('Operador')->getRecord($usuario['callcenter']);

        $avaliacoes = $serviceAvaliacoes->getAvaliacoesRespondidas(array('empresa' => $operador->empresa));

        //paginar avaliacoes
        $Paginator = new Paginator(new ArrayAdapter($avaliacoes->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);

        //verificar se existem avaliações em aberto
        $avaliacoesAbertas = $servicePilhaAvaliacoes->getAvaliacoesAbertas(date('Y-m-d'), $operador->empresa);
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
        $this->layout('layout/callcenter');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $operadorCallCenter = $this->getServiceLocator()->get('Operador')->getRecord($usuario['callcenter']);
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(8, $operadorCallCenter['empresa'])->toArray();

        //desabilitar campos
        $formAvaliacao = new AvaliacaoForm('formAvaliacao', $this->getServiceLocator(), $campos);
        $formAvaliacao->desabilitarCampos();

        //pesquisar período de avaliação do callcenter
        $servicePilhaAvaliacoes = $this->getServiceLocator()->get('PilhaAvaliacaoCallCenter');
        //Pegar sempre a avaliação em aberto com menor data de referência
        $avaliacoes = $servicePilhaAvaliacoes->getAvaliacoesAbertas(date('Y-m-d'), $operadorCallCenter['empresa'])->toArray();
        if(!isset($avaliacoes[0])){
            return $this->redirect()->toRoute('listarAvaliacoesCallCenter');
        }
        $avaliacao = $avaliacoes[0];


        if(!$avaliacao){
            $this->flashMessenger()->addInfoMessage('Nenhuma avaliação em aberto encontrada!');
            return $this->redirect()->toRoute('listarAvaliacoesCallCenter');
        }
        $dataReferencia = explode('-', $avaliacao['data_referencia']);
        $formAvaliacao = $this->pesquisarMetasPopularForm($formAvaliacao, $operadorCallCenter['empresa'], $dataReferencia['0'], $dataReferencia['1'], $dataReferencia['2']);

        if(!$formAvaliacao){
            return $this->redirect()->toRoute('listarAvaliacoesCallCenter');
        }
        //se já passou das 11 da manhã não pode mais responder a avaliação
        if(date('H') >= 11){
            $this->flashMessenger()->addWarningMessage('Avaliações só podem ser respondidas até as 11 da manhã!');
            return $this->redirect()->toRoute('listarAvaliacoesCallCenter');
        }

        
        //verificar se não for fim de semana ou feriado subtrai um dia
        $subtrairDias = true;
        if($this->definirDias()->diaNaoUtil($avaliacao['data_referencia'])){
            $subtrairDias = false;
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formAvaliacao->setData($dados);

            //remover os campos calculados
            $formAvaliacao->removerCampos();
            if($formAvaliacao->isValid()){
                $dados = $formAvaliacao->getData();

                if($subtrairDias){
                    $this->diasRestantes = $this->diasRestantes-1;
                }
                $dados['meta_agendamento']  = $this->meta->valor;
                $dados['diferenca']         = $dados['agendamento'] - $this->meta->valor;
                $dados['empresa']         = $operadorCallCenter->empresa;
                $dados['usuario']         = $usuario['id'];
                $dados['data_referencia'] = $avaliacao['data_referencia'];
                $dados['meta_agendamento'] = $this->meta->valor;
                $this->metaMensalRestante = $this->calcularMetaMensal($operadorCallCenter['empresa'], $this->metaMensal, $dados['agendamento']);
                $dados['meta_agendamento_mensal'] = $this->metaMensalRestante;
                $diasRestantes = $this->diasRestantes;
                if($diasRestantes == 0){
                    $diasRestantes = 1;
                }
                $dados['meta_agendamento_mensal_dias'] = number_format($this->metaMensalRestante/($diasRestantes), 2, '.', '');
                $dados['dias_meta'] = "$this->diasRestantes";
                $dados['meta_mensal'] = $this->metaMensal;
                
                $idAvaliacao = $this->getServiceLocator()->get('AvaliacaoCallCenter')->insert($dados);
                $this->flashMessenger()->addSuccessMessage('Avaliação inserida com sucesso!');

                if(count($avaliacoes) > 1){
                    return $this->redirect()->toRoute('avaliacaoCallCenter');
                }else{
                    return $this->redirect()->toRoute('listarAvaliacoesCallCenter');
                }
            }
        }

        return new ViewModel(array(
                'formAvaliacao' => $formAvaliacao,
                'avaliacao'     => $avaliacao,
                'campos'        => $campos,
                'avaliacoes'    => $avaliacoes,
                'diasRestantes' => $this->diasRestantes,
                'metaDiaria'    => $this->meta->valor,
                'metaMensal'    => $this->metaMensalRestante,
                'subtrairDias'  => $subtrairDias

            ));

    }

    private function pesquisarMetasPopularForm($formAvaliacao, $empresa, $ano, $mes, $dia){
        //pesquisar meta para a data corrente
        $servicemetaDiaria = $this->getServiceLocator()->get('MetaAgendamento');
        $this->meta = $servicemetaDiaria->getRecordFromArray(array(
                                            'ano'       => $ano,
                                            'mes'       => $mes,
                                            'empresa'   => $empresa
                                        ));
        if(!$this->meta){
            $this->flashMessenger()->addWarningMessage('Não existe meta de agendamento diária para o mês corrente!');
            return false;
        }

        //pesquisar quanto falta para meta mensal
        $this->metaMensal = $this->getServiceLocator()
                            ->get('MetaAgendamentoMensal')
                            ->getRecordFromArray(array(
                                            'ano'           => $ano,
                                            'mes'           => $mes,
                                            'empresa'        => $empresa
                                        ));
        $this->metaMensal = $this->metaMensal->valor;
        $this->metaMensalRestante = $this->calcularMetaMensal($empresa, $this->metaMensal, 0, $mes);
        
        if(!$this->metaMensal){
            $this->flashMessenger()->addWarningMessage('Não existe meta de agendamento mensal para o mês corrente!');
            return false;
        }

        //calcular quantos dias úteis faltam para o fim do mês, dividir meta mensal por dias
        $this->diasRestantes = $this->definirDias()->diasUteisParaFimMes($ano, $mes, $dia);
        $diasRestantes = $this->diasRestantes;
        if(empty($this->diasRestantes)){
            $this->diasRestantes = 0;
            $diasRestantes = 1;
        }
        $this->metaDiasRestam = number_format($this->metaMensalRestante/$diasRestantes, 2, '.', '');

        //popular form com meta
        $formAvaliacao->setData(array(
                                    'meta_agendamento' => $this->meta->valor, 
                                    'meta_agendamento_mensal' => $this->metaMensalRestante,
                                    'meta_agendamento_mensal_dias' => $this->metaDiasRestam,
                                    'dias_meta' => $this->diasRestantes,
                                    'meta_mensal' => $this->metaMensal
                                    ));
        return $formAvaliacao;
    }

    private function calcularMetaMensal($empresa, $metaMensal, $realizado = 0, $mes = false){
        //pesquisar meta mensal
        if(!$metaMensal){
            return false;
        }

        if(!$mes){
            $mes = date('m');
        }

        //pesquisar avaliações respondida do mês
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter')->getAvaliacoesByMesEmpresa($empresa, $mes);

        //somar agendamentos realizados no mês
        foreach ($avaliacoes as $avaliacao) {
            $realizado += $avaliacao->agendamento;
        }

        //subtrair meta mensal - soma de agendamentos realizados
        return $metaMensal - $realizado;

    }

    public function visualizaravaliacaoAction(){
        $this->layout('layout/callcenter');
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');

        //pesquisar avaliação
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoCallCenter');
        $avaliacao = $serviceAvaliacao->getAvaliacacoById($idAvaliacao);
        if(!$avaliacao){
            $this->flashMessenger()->addWarningMessage('Nenhuma avaliação encontrada!');
            return $this->redirect()->toRoute('listarAvaliacoesCallCenter');
        }
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(8, $avaliacao->empresa)->toArray();
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
        $this->layout('layout/admincallcenter');

        //gerar form de pesquisa
        $formGrafico = new graficoForm('formGrafico', $this->getServiceLocator());
        
        return new ViewModel(array(
                'formGrafico'       =>  $formGrafico
            ));
    }

    public function visualizargraficoAction(){
        $this->layout('layout/admincallcenter');
        $formGrafico = new graficoForm('formGrafico', $this->getServiceLocator());
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            //se for do tipo excell redirencionar para interface de relatório
            if($dados['tipoRelatiorio'] == 2){
                //excel layout 1
                $sessao = new Container();
                $sessao->offsetSet('inicio', $formGrafico->converterData($dados['inicio']));
                $sessao->offsetSet('fim', $formGrafico->converterData($dados['fim']));
                $sessao->offsetSet('empresa', $dados['empresa']);
                return $this->redirect()->toRoute('planilhaCallCenter');
            }else{
                if($dados['tipoRelatiorio'] == 3){
                    //excel layout 2
                    $sessao = new Container();
                    $sessao->offsetSet('inicio', $formGrafico->converterData($dados['inicio']));
                    $sessao->offsetSet('fim', $formGrafico->converterData($dados['fim']));
                    $sessao->offsetSet('empresa', $dados['empresa']);
                    return $this->redirect()->toRoute('planilhaCallCenter2');
                }else{
                    if($dados['tipoRelatiorio'] == 4){
                        //relatório de banco de dados
                        $sessao = new Container();
                        $sessao->offsetSet('inicio', $formGrafico->converterData($dados['inicio']));
                        $sessao->offsetSet('fim', $formGrafico->converterData($dados['fim']));
                        $sessao->offsetSet('empresa', $dados['empresa']);
                        return $this->redirect()->toRoute('planilhaBancoDados');  
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
            $avaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter')->getAvaliacoesByParams($dados);
            
            //pesquisar campos
            $somaAvaliacoes = $this->somarCamposPlanilha($avaliacoes);
        }

        if(!$avaliacoes || $avaliacoes->count() < 1){
            $this->flashMessenger()->addWarningMessage('Nenhuma avaliação encontrada!');
            return $this->redirect()->toRoute('administradorAvaliacoesCallCenter');
        }

        //se for adm ele precisa tirar alguns itens do breadcrumb
        $usuario = $this->getServiceLocator()->get('session')->read();
        return new ViewModel(array(
            'somaAvaliacoes'    => $somaAvaliacoes,
            'usuario'           => $usuario
        ));
    }

    private function somarCamposPlanilha($avaliacoes){
        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(8)->toArray();
        $somaAvaliacoes = array();
        
        foreach ($campos as $campo) {
            $somaAvaliacoes[$campo['nome_campo']]['valor'] = 0;
            $somaAvaliacoes[$campo['nome_campo']]['label'] = $campo['label'];
            if($campo['id'] == 110){
                //diferença
                $somaAvaliacoes['diferenca']['valor'] = 0;
                $somaAvaliacoes['diferenca']['label'] = 'Diferença entre a meta e o realizado RM';

            }
        }

        foreach ($avaliacoes as $avaliacao) {
            foreach ($campos as $campo) {
                if($campo['categoria_questao'] != 32){
                    $somaAvaliacoes[$campo['nome_campo']]['valor'] += $avaliacao[$campo['nome_campo']];
                }
            }
        }
        $somaAvaliacoes['diferenca']['valor'] = $somaAvaliacoes['meta_agendamento']['valor'] - $somaAvaliacoes['agendamento']['valor'];
        return $somaAvaliacoes;
    }

    public function graficopersonalizadoAction(){
        //verificar se veve loimpar os parametros
        $serviceSubGrafico = $this->getServiceLocator()->get('CallcenterGraficoSub');
        $limpar = $this->params()->fromRoute('limpar');
        if($limpar == 'S'){
            $serviceSubGrafico->delete(array(1 => 1));
            $this->flashMessenger()->addSuccessMessage('Parâmetros excluídos com sucesso!');
            return $this->redirect()->toRoute('graficoPersonalizado');
        }

        $this->layout('layout/admincallcenter');
        $formGrafico = new graficoPersonalizadoForm('formGrafico');

        //pesquisar se existe alguma data registrada, caso sim abrir subform
        $serviceDatasGrafico = $this->getServiceLocator()->get('CallcenterGrafico');
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
                    return $this->redirect()->toRoute('graficoPersonalizado');
                }
            }else{
                if(isset($dados->empresa)){
                    //salvar empresa
                    if($dados['empresa'] == 'T'){
                        $empresas = $this->getServiceLocator()->get('Empresa')
                                        ->getRecordsFromArray(
                                            array('ativo' => 'S', 'callcenter' => 'S'), 
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
                    return $this->redirect()->toRoute('graficoPersonalizado');
                }else{
                    //salvar campo do formulário que deve ser exibido
                    $serviceSubGrafico->insert(array('campo' => $dados->campo));
                    $this->flashMessenger()->addSuccessMessage('Campo vinculado com sucesso!');
                    return $this->redirect()->toRoute('graficoPersonalizado');
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
        $this->layout('layout/admincallcenter');
        $serviceparametros = $this->getServiceLocator()->get('CallcenterGraficoSub');

        //pesquisar datas 
        $datas = $this->getServiceLocator()->get('CallcenterGrafico')->getRecord(1);

        //pesquisar empresas
        $empresas = $serviceparametros->getEmpresas()->toArray();
        if(count($empresas) == 0){
            $this->flashMessenger()->addWarningMessage('Nenhuma empresa selecionada!');
            return $this->redirect()->toRoute('graficoPersonalizado');
        }
        $idEmpresas = array();
        foreach ($empresas as $empresa) {
            $idEmpresas[] = $empresa['id_empresa'];
        }
        
        //pesquisar campos
        $campos = $serviceparametros->getCampos()->toArray();
        if(count($campos) == 0){
            $this->flashMessenger()->addWarningMessage('Nenhum campo selecionado!');
            return $this->redirect()->toRoute('graficoPersonalizado');
        }
        $nomesCampos = array();
        foreach ($campos as $campo) {
            $nomesCampos[] = $campo['nome_campo'];
        }

        //pesquisar avaliação 
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoCallCenter');
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

    public function planilhacallcenterAction(){
        $sessao = new Container();
        $formPesquisa = new graficoPersonalizadoForm('formPesquisa');
        $dados = array(
                'inicio'    =>  $sessao->offsetGet('inicio'),
                'fim'       =>  $sessao->offsetGet('fim'),
                'empresa'   =>  $sessao->offsetGet('empresa')
            );


        //pesquisar avaliações por período, se vier empresa, pesquisar também por empresa
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter')->getAvaliacoesToExcell($dados)->toArray();
        
        //gerar relatório
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações callcenter");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        //mesclar celular para imagem
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:R6');
        //data de referencia
        $objPHPExcel->getActiveSheet()->mergeCells('A7:B7');
        $dataReferencia = $avaliacoes[0]['data_referencia'];
        $this->numeroLinhas = 7;

        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(8)->toArray();

        $periodoReferencia = $formPesquisa->converterData($dados['inicio']).' a '.$formPesquisa->converterData($dados['fim']);
        $objPHPExcel = $this->escreverCabecalhoExcel($objPHPExcel, $periodoReferencia, $campos);

        //dados das empresas
        $numeroLetra = 3;
        $arrayLetras = $this->alfabeto();

        //somar valores da mesma empresa
        $avaliacoes = $this->somarCamposByEmpresa($avaliacoes, $campos);
        //colocar campos Estado
        $estado = 'N';
        $numeroLinhas = 0;

        $objPHPExcel->getActiveSheet()->getStyle("A7:R7")->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        foreach ($avaliacoes as $avaliacao) {
            if($avaliacao['id_estado'] != $estado){
                if($estado != 'N'){
                    $letra = $arrayLetras[$numeroLetra];
                    $objPHPExcel->getActiveSheet()->SetCellValue($letra.'7', $nomeEstado);

                    $numeroLetra += $numeroLinhas;
                    if($numeroLinhas > 1){
                        $objPHPExcel->getActiveSheet()->mergeCells($letra.'7:'.$arrayLetras[$numeroLetra].'7');
                    }
                }
                $numeroLinhas = 0;
                $estado = $avaliacao['id_estado'];
                $nomeEstado = $avaliacao['nome_estado'];
            }
            $numeroLinhas++;
        }

        if($numeroLinhas > 0){
            //Escrever último estado
            $letra = $arrayLetras[$numeroLetra];
            $objPHPExcel->getActiveSheet()->SetCellValue($letra.'7', $nomeEstado);
            if($numeroLinhas > 1){
                $numeroLetra += $numeroLinhas - 1;
                $objPHPExcel->getActiveSheet()->mergeCells($letra.'7:'.$arrayLetras[$numeroLetra].'7');
            }
        }

        $numeroLetra = 3;
        foreach ($avaliacoes as $avaliacao) {
                $letra = $arrayLetras[$numeroLetra];
                $objPHPExcel = $this->escreverColunaRelatorio($objPHPExcel, $letra, $avaliacao, $campos);
                $numeroLetra++;
        }

        $letra = $arrayLetras[$numeroLetra];
        
        //SETAR LARGURA DE COLUNAS
        foreach(range('A','Z') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                ->setWidth(30);
        }

        $objPHPExcel->getActiveSheet()->getStyle('A1:Z50')
                ->getAlignment()->setWrapText(true); 


        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(70);
        
        //inserir imagem
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
        
        $diretorio = 'public/relatorios/callcenter';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/'.date('d-m-Y').'-callcenter.xlsx';
        $objWriter->save($fileName);
        
        //salvar caminho do arquivo na sessão
        $sessao = new Container();
        $sessao->offsetSet('xlsx', $fileName);

        //enviar para download
        return $this->redirect()->toRoute('downloadPlanilha');
    }

    public function planilhacallcenter2Action(){
        $sessao = new Container();
        $formPesquisa = new graficoPersonalizadoForm('formPesquisa');
        $dados = array(
                'inicio'    =>  $sessao->offsetGet('inicio'),
                'fim'       =>  $sessao->offsetGet('fim'),
                'empresa'   =>  $sessao->offsetGet('empresa')
            );

        if(empty($dados['empresa'])){
            $this->flashMessenger()->addWarningMessage('Selecione uma clínica para a planílha individual!');
            return $this->redirect()->toRoute('grafico');
        }

        //pesquisar avaliações por período, se vier empresa, pesquisar também por empresa
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter')->getAvaliacoesToExcell($dados)->toArray();

        //gerar relatório
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações callcenter");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        
        $objPHPExcel->getActiveSheet()->mergeCells('A1:R6');
        $this->numeroLinhas = 7;

        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(8)->toArray();
        $objPHPExcel = $this->escreverCabecalhoExcelDiario($objPHPExcel, $avaliacoes[0]['nome_empresa'], $campos);

        //dados das empresas
        $numeroLetra = 2;
        $arrayLetras = $this->alfabeto();

        foreach ($avaliacoes as $avaliacao) {
                $letra = $arrayLetras[$numeroLetra];
                $objPHPExcel = $this->escreverColunaRelatorioDiario($objPHPExcel, $letra, $avaliacao, $campos);
                $numeroLetra++;
        }
        

        foreach($arrayLetras as $columnID) {
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
        
        $diretorio = 'public/relatorios/callcenter';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/'.date('d-m-Y').'-callcenter.xlsx';
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
                    if($campo['nome_campo'] != 'problema' && $campo['nome_campo'] != 'acao_gestor'){
                        if($campo['nome_campo'] == 'tempo_espera' 
                            || $campo['nome_campo'] == 'tempo_atendimento' 
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
            }else{
                //gerar médias de %
                $somaAvaliacoes = $this->somaPorcentagem($somaAvaliacoes, $keySoma, $contMedia);
                $somaAvaliacoes[$keySoma]['meta_agendamento'] = $avaliacao['meta_agendamento'];
                $data = explode('-', $avaliacao['data_referencia']);
                $diasMes = cal_days_in_month(CAL_GREGORIAN, $data[1], $data[0]);
                $somaAvaliacoes[$keySoma]['meta_agendamento_mensal'] =  $avaliacao['meta_mensal'] - $somaAvaliacoes[$keySoma]['agendamento'];
                $somaAvaliacoes[$keySoma]['meta_agendamento_mensal_dias'] = $avaliacao['meta_mensal']/$diasMes;
                $somaAvaliacoes[$keySoma]['meta_mensal'] = $avaliacao['meta_mensal'];
                $somaAvaliacoes[$keySoma]['dias_meta'] = $this->definirDias()->diasUteisParaFimMes($data[0], $data[1], '01');
                $somaAvaliacoes[$keySoma]['diferenca'] = $avaliacao['meta_mensal'] - $somaAvaliacoes[$keySoma]['agendamento'];
                

            }
        }
        //gerar médias de % da última coluna
        $somaAvaliacoes = $this->somaPorcentagem($somaAvaliacoes, $keySoma, $contMedia);
        
        return $somaAvaliacoes;
    }

    private function somaPorcentagem($avaliacoes, $key, $numeroMedia){
        $avaliacoes[$key]['agendamento_geral_porcentagem'] = number_format($avaliacoes[$key]['agendamento_geral_porcentagem']/$numeroMedia,2).'%';
        $avaliacoes[$key]['abandonadas_atendidas_hora'] = number_format($avaliacoes[$key]['abandonadas_atendidas_hora']/$numeroMedia,2).'%';
        $avaliacoes[$key]['abandonadas_atendidas_dia'] = number_format($avaliacoes[$key]['abandonadas_atendidas_dia']/$numeroMedia,2).'%';
        $avaliacoes[$key]['abandono_callback'] = number_format($avaliacoes[$key]['abandono_callback']/$numeroMedia,2).'%';
        $avaliacoes[$key]['confirmacao_geral'] = number_format($avaliacoes[$key]['confirmacao_geral']/$numeroMedia,2).'%';
        $avaliacoes[$key]['d0'] = number_format($avaliacoes[$key]['d0']/$numeroMedia,2).'%';
        $avaliacoes[$key]['d1'] = number_format($avaliacoes[$key]['d1']/$numeroMedia,2).'%';
        $avaliacoes[$key]['d2'] = number_format($avaliacoes[$key]['d2']/$numeroMedia,2).'%';
        return $avaliacoes;
    }

    private function escreverCabecalhoExcel($objPHPExcel, $dataReferencia, $campos){
        //data escrito em vermelho
        $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:R$this->numeroLinhas")->applyFromArray(array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FF0000'),
                'size'  => 10,
            )
        ));


        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, $dataReferencia);

        $this->numeroLinhas++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, 'INDICADORES CALL CENTER');
        $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:R$this->numeroLinhas")->applyFromArray(array(
            'font'  => array(
                'bold'  => true,
            )
        ));
        
        //cinza claro
        $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:R$this->numeroLinhas")->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                 'rgb' => 'CFCFCF'
            )
        ));

        $arrayCinzaEscuro = array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                     'rgb' => 'B5B5B5'
                )
            );

        setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
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

            $objPHPExcel->getActiveSheet()->getStyle("A$this->numeroLinhas:A$this->numeroLinhas")->applyFromArray(array(
                'font'  => array(
                    'bold'  => true,
                )
            ));
        }

        $this->numeroLinhas++;

        return $objPHPExcel;
    }

    private function escreverColunaRelatorio($objPHPExcel, $letra, $avaliacao, $campos){
        $linha = 8;
        $objPHPExcel->getActiveSheet()->getStyle($letra.$linha.':'.$letra.'44')->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        $objPHPExcel->getActiveSheet()->SetCellValue($letra.$linha, strtoupper($avaliacao['nome_empresa']));
        $linha++;

        $categoriaQuestao = 0;
        foreach ($campos as $campo) {
            if($categoriaQuestao != $campo['categoria_questao']){
                $linha++;
                $categoriaQuestao = $campo['categoria_questao'];
            }

            $objPHPExcel->getActiveSheet()->SetCellValue($letra.$linha, preg_replace('/\s/',' ',$avaliacao[$campo['nome_campo']]));
            $linha++;
        }

        return $objPHPExcel;
    }

    private function escreverCabecalhoExcelDiario($objPHPExcel, $empresa, $campos){
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$this->numeroLinhas, 'INDICADORES CALL CENTER - '.$empresa);
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

        $parametros = array('data_referencia' => date('Y-m-d'), 'respondidas' => 'N');
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
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoCallCenter');
        
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
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);

        //gerar cabeçalho
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Unidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Data de referência');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Categoria');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Indicador');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Resultado');

        //pesquisar campos
        $campos = $this->getServiceLocator()->get('Campo')->getCamposByAba(8)->toArray();
        
        //pesquisar avaliações para o período
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter')->getAvaliacoesToExcell2($dados)->toArray();

        //percorrer avaliações
        $numeroLinhas = 2;
        foreach ($avaliacoes as $avaliacao) {
            foreach ($campos as $campo) {
                //percorrer campos e inserir no relatório
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numeroLinhas, $avaliacao['nome_empresa']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numeroLinhas, $formPesquisa->converterData($avaliacao['data_referencia']));
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numeroLinhas, $campo['nome_categoria']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numeroLinhas, $campo['label']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numeroLinhas, $avaliacao[$campo['nome_campo']]);
                $numeroLinhas++;
            }
        }

                
        // Save Excel 2007 file
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        
        $diretorio = 'public/relatorios';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/relatorioBanco.xlsx';
        $objWriter->save($fileName);
        
        //salvar caminho do arquivo na sessão
        $sessao = new Container();
        $sessao->offsetSet('xlsx', $fileName);

        //enviar para download
        return $this->redirect()->toRoute('downloadPlanilha');

    }

    public function novaavaliacaoadmAction(){
        $this->layout('layout/callcenter');

        //pegar dados da url
        $empresa = $this->params()->fromRoute('empresa');
        $dataReferencia = $this->params()->fromRoute('data');
        $vetorDataReferencia = explode('-', $dataReferencia);

        //validar url params
        if(empty($empresa) || empty($dataReferencia)){
            $this->flashMessenger()->addWarningMessage('Parâmetros inválidos!');
            return $this->redirect()->toRoute('administradorAvaliacoesCallCenter');
        }

        //pesquisar avaliação
        $serviceCallcenter = $this->getServiceLocator()->get('AvaliacaoCallCenter');
        $avaliacao = $serviceCallcenter->getRecordFromArray(array('empresa' => $empresa, 'data_referencia' => $dataReferencia));

        //se existe redirecionar para alterar avaliacao
        if($avaliacao){
            $this->flashMessenger()->addInfoMessage('Avaliação já existe no sistema!');
            $this->redirect()->toRoute('alterarAvaliacaoAdm', array('id' => $avaliacao['id']));
        }

        //se não existe instanciar form
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(8, $empresa)->toArray();

        //desabilitar campos
        $formAvaliacao = new AvaliacaoForm('formAvaliacao', $this->getServiceLocator(), $campos);
        $formAvaliacao->desabilitarCampos();
        $formAvaliacao = $this->pesquisarMetasPopularForm($formAvaliacao, 
                                                          $empresa, 
                                                          $vetorDataReferencia[0], 
                                                          $vetorDataReferencia[1], 
                                                          $vetorDataReferencia[2]
                                                        );

        //verificar se precisa subtrair dias
        $subtrairDias = true;
        if($this->definirDias()->diaNaoUtil($dataReferencia)){
            $subtrairDias = false;
        }
        
        //se post salvar dados
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formAvaliacao->setData($dados);

            //remover os campos calculados
            $formAvaliacao->removerCampos();

            if($subtrairDias){
                $this->diasRestantes = $this->diasRestantes-1;
            }

            if($formAvaliacao->isValid()){
                $dados = $formAvaliacao->getData();
                $usuario = $this->getServiceLocator()->get('session')->read();
                $dados['diferenca']         = $dados['agendamento'] - $this->meta->valor;
                $dados['empresa']         = $empresa;
                $dados['usuario']         = $usuario['id'];
                $dados['data_referencia'] = $dataReferencia;
                $dados['meta_agendamento'] = $this->meta->valor;
                $this->metaMensalRestante = $this->calcularMetaMensal($empresa, $this->metaMensal, $dados['agendamento'], $vetorDataReferencia[1]);
                $dados['meta_agendamento_mensal'] = $this->metaMensalRestante;
                $diasRestantes = $this->diasRestantes;
                $dados['dias_meta'] = "$diasRestantes";
                if($diasRestantes == 0){
                    $diasRestantes = 1;
                }
                $dados['meta_agendamento_mensal_dias'] = number_format($this->metaMensalRestante/$diasRestantes, 2, '.', '');
                $dados['meta_mensal'] = $this->metaMensal;
                $dados['auditado'] = 'I';

                $idAvaliacao = $this->getServiceLocator()->get('AvaliacaoCallCenter')->insert($dados);
                $this->flashMessenger()->addSuccessMessage('Avaliação inserida com sucesso!');

                return $this->redirect()->toRoute('administradorAvaliacoesCallCenter');
            }
        }

        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($empresa);
        return new ViewModel(array(
                'formAvaliacao'     => $formAvaliacao,
                'diasRestantes'     => $this->diasRestantes,
                'metaDiaria'        => $this->meta->valor,
                'metaMensal'        => $this->metaMensalRestante,
                'dataReferencia'    => $dataReferencia,
                'campos'            => $campos,
                'empresa'           => $empresa,
                'subtrairDias'      => $subtrairDias

            ));


    }

    public function alteraravaliacaoadmAction(){
        $this->layout('layout/callcenter');
        $serviceAvaliacao = $this->getServiceLocator()->get('AvaliacaoCallCenter');

        //pesquisar avaliação por id
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');
        $avaliacao = $serviceAvaliacao->getRecord($idAvaliacao);
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(8, $avaliacao['empresa'])->toArray();
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

                return $this->redirect()->toRoute('administradorAvaliacoesCallCenter');
            }
        }
        
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($avaliacao['empresa']);
        return new ViewModel(array(
                'formAvaliacao'     => $formAvaliacao,
                'diasRestantes'     => $avaliacao['dias_meta'],
                'metaDiaria'        => $avaliacao['meta_agendamento'],
                'metaMensal'        => $avaliacao['meta_agendamento_mensal'],
                'dataReferencia'    => $avaliacao['data_referencia'],
                'campos'            => $campos,
                'empresa'           => $empresa

            ));
    }

/*     public function corrigirbugdatasAction(){
        $campos = $this->getServiceLocator()->get('CampoEmpresa')->getCamposEmpresaByAba(8, 81)->toArray();

        //desabilitar campos
        $formAvaliacao = new AvaliacaoForm('formAvaliacao', $this->getServiceLocator(), $campos);
        $formAvaliacao->desabilitarCampos();

        //selecionar todas as avaliações do mes 4
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter')->avaliacoesByPeriodo();
        foreach ($avaliacoes as $avaliacao) {
            //recalcular metas de todas as avaliações
            $dataReferencia = explode('-', $avaliacao['data_referencia']);
            $formAvaliacao = $this->pesquisarMetasPopularForm($formAvaliacao, $avaliacao['empresa'], $dataReferencia['0'], $dataReferencia['1'], $dataReferencia['2']);
            $subtrairDias = true;
            
            $dados['meta_agendamento'] = $this->meta->valor;
            $dados['diferenca']         = $avaliacao['agendamento'] - $this->meta->valor;
            $dados['meta_agendamento_mensal'] = $this->calcularMetaMensalbug($dataReferencia[2], $dataReferencia[1], $avaliacao);
            $dados['dias_meta'] = $this->diasRestantes;
            if($this->diasRestantes == 0){
                $this->diasRestantes = 1;
            }
            $dados['meta_agendamento_mensal_dias'] = number_format($dados['meta_agendamento_mensal']/$this->diasRestantes, 2, '.', '');
            $dados['meta_mensal'] = $this->metaMensal;
        
            //gerar sql com correção das metas
            echo 'UPDATE tb_callcenter SET meta_agendamento = '.$dados['meta_agendamento'].', diferenca = '.$dados['diferenca'].
            ', meta_agendamento_mensal = '.$dados['meta_agendamento_mensal'].', meta_agendamento_mensal_dias = '.$dados['meta_agendamento_mensal_dias'].
            ', dias_meta = '.$dados['dias_meta'].', meta_mensal = '.$dados['meta_mensal'].' WHERE id = '.$avaliacao['id'].';<br>';
            //exibir na tela
        }
        //var_dump($avaliacoes->count());
        die();
    }



      private function calcularMetaMensalbug($dia, $mes, $avaliacaoAtual){
        //pesquisar avaliações respondida do mês
        $avaliacoes = $this->getServiceLocator()->get('AvaliacaoCallCenter')->getAvaliacoesByMesEmpresa($avaliacaoAtual['empresa'], $mes);
        $realizado = 0;
        //somar agendamentos realizados no mês
        foreach ($avaliacoes as $avaliacao) {
            $dataReferencia = explode('-', $avaliacao['data_referencia']);
            if($dataReferencia[2] <= $dia){
                $realizado += $avaliacao->agendamento;
            }
        }

        //subtrair meta mensal - soma de agendamentos realizados
        return $this->metaMensal - $realizado;

    }*/
}