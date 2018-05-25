<?php

namespace Remuneracaomedica\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;


use Zend\Session\Container;

class SimuladorController extends BaseController
{
    private $indicadores, $valoresCalculados;
    public function simuladorAction()
    {
        $this->layout('layout/remuneracaomedica');
        
        return new ViewModel(array(
        		
        	));
    }

    public function relacionamentoAction(){
        $this->layout('layout/remuneracaomedica');
        
        //pesquisar os indicadores do medico selecionado
        $medico = 0;
        if($this->getRequest()->isPost()){
            $medico = $this->getRequest()->getPost();
            $medico = $medico->medico;
        }

        $indicadores = array();
        $indicadores = $this->getServiceLocator()->get('MedicosSimulador')->getIndicadores($medico)->toArray();
        if(count($indicadores) > 0){
            $this->calcularValores($indicadores);
        }
        
        $medicos = $this->getServiceLocator()->get('Medico')->getRecordsFromArray(array('ativo' => 'S'), 'nome_medico');

        return new ViewModel(array(
                'indicadores'           => $this->indicadores,
                'valoresCalculados'     => $this->valoresCalculados,
                'totalIndicadores'      => count($this->indicadores),
                'medicos'               => $medicos,
                'idMedico'                => $medico
            ));
    }

    private function calcularValores($indicadores){
        $valoresCalculados = array();
        //somar total
        $linhas = 0;
        if(count($indicadores) > 0){
            foreach ($indicadores as $key => $indicador) {
                $indicadores[$key]['media_exames'] = $indicador['exames']/$indicador['periodos'];
                $linhas++;
                if($linhas == 1){
                    $valoresCalculados['totais'] = $indicador;
                    $valoresCalculados['totais']['media_exames'] = $indicadores[$key]['media_exames'];
                    continue;    
                }
                $valoresCalculados['totais']['periodos']            += $indicador['periodos'];
                $valoresCalculados['totais']['exames']              += $indicador['exames'];
                $valoresCalculados['totais']['eficiencia']          += $indicador['eficiencia'];
                $valoresCalculados['totais']['media_exames']        += $indicadores[$key]['media_exames'];
                $valoresCalculados['totais']['efetividade']         += $indicador['efetividade'];
                $valoresCalculados['totais']['satisfacao_paciente'] += $indicador['satisfacao_paciente'];
                $valoresCalculados['totais']['educacao_pesquisa']   += $indicador['educacao_pesquisa'];
                $valoresCalculados['totais']['pontuacao']           += $indicador['pontuacao'];
            }
        }

        //medias
        $valoresCalculados['medias']['periodos']            = $valoresCalculados['totais']['periodos']/$linhas;
        $valoresCalculados['medias']['exames']              = $valoresCalculados['totais']['exames']/$linhas;
        $valoresCalculados['medias']['eficiencia']          = $valoresCalculados['totais']['eficiencia']/$linhas;
        $valoresCalculados['medias']['media_exames']        = $valoresCalculados['totais']['media_exames']/$linhas;
        $valoresCalculados['medias']['efetividade']         = $valoresCalculados['totais']['efetividade']/$linhas;
        $valoresCalculados['medias']['satisfacao_paciente'] = $valoresCalculados['totais']['satisfacao_paciente']/$linhas;
        $valoresCalculados['medias']['educacao_pesquisa']   = $valoresCalculados['totais']['educacao_pesquisa']/$linhas;
        $valoresCalculados['medias']['pontuacao']           = $valoresCalculados['totais']['pontuacao']/$linhas;

        $this->valoresCalculados = $valoresCalculados;
        $this->indicadores = $indicadores;
    }

}