<?php

namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Container;
use Zend\Authentication\AuthenticationService;

class DefinirDias extends AbstractPlugin
{   
    private $feriados = false;
    public function finalSemana($dataSTR){
        $data = explode('-',$dataSTR);

        //time do dia que sera o dia final do prazo previo
        $time = mktime(0, 0, 0, $data[1], intval($data[2]), $data[0]);

        $diaSemana = date("w", $time);

        $finalSemana = array();
        switch($diaSemana){
            case 0: //domingo
                return true;
            case 6: //sabado
                return true;
            default: 
            return false;
        }

    }

    public function feriado($data, $ano = null){
        //verifica feriados
        if ($ano === null){
            $ano = intval(date('Y'));
        }
 
        $pascoa = easter_date($ano); // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
        $dia_pascoa = date('j', $pascoa);
        $mes_pascoa = date('n', $pascoa);
        $ano_pascoa = date('Y', $pascoa);

        if(!$this->feriados){
            $this->feriados = array(
                // Tatas Fixas dos feriados Nacionail Basileiras
                date("Y-m-d", mktime(0, 0, 0, 1,  1,   $ano)), // Confraternização Universal - Lei nº 662, de 06/04/49
                date("Y-m-d", mktime(0, 0, 0, 4,  21,  $ano)), // Tiradentes - Lei nº 662, de 06/04/49
                date("Y-m-d", mktime(0, 0, 0, 5,  1,   $ano)), // Dia do Trabalhador - Lei nº 662, de 06/04/49
                date("Y-m-d", mktime(0, 0, 0, 9,  7,   $ano)), // Dia da Independência - Lei nº 662, de 06/04/49
                date("Y-m-d", mktime(0, 0, 0, 10,  12, $ano)), // N. S. Aparecida - Lei nº 6802, de 30/06/80
                date("Y-m-d", mktime(0, 0, 0, 11,  2,  $ano)), // Todos os santos - Lei nº 662, de 06/04/49
                date("Y-m-d", mktime(0, 0, 0, 11, 15,  $ano)), // Proclamação da republica - Lei nº 662, de 06/04/49
                date("Y-m-d", mktime(0, 0, 0, 12, 25,  $ano)), // Natal - Lei nº 662, de 06/04/49

                // These days have a date depending on easter
                date("Y-m-d", mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48,  $ano_pascoa)),//2ºferia Carnaval
                date("Y-m-d", mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47,  $ano_pascoa)),//3ºferia Carnaval 
                date("Y-m-d", mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2 ,  $ano_pascoa)),//6ºfeira Santa  
                date("Y-m-d", mktime(0, 0, 0, $mes_pascoa, $dia_pascoa     ,  $ano_pascoa)),//Pascoa
                date("Y-m-d", mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60,  $ano_pascoa)),//Corpus Cirist
            );
            
        }
    
        if(in_array($data, $this->feriados)){
            return true;
        }

        return false;
    }


    public function diaNaoUtil($data){
        if($this->finalSemana($data) || $this->feriado($data)){
            return true;
        }

        return false;
    }

    public function subtrairDias($data, $diasSubtrair){
        $data = explode('-', $data);
        return date('Y-m-d', mktime(0, 0, 0, $data[1], intval($data[2]) - $diasSubtrair, $data[0]));
    }

    public function somarDias($data, $diasSomar){
        $data = explode('-', $data);
        return date('Y-m-d', mktime(0, 0, 0, $data[1], intval($data[2]) + $diasSomar, $data[0]));
    }

    public function feriadosAnteriores($dataAvaliacao){
        //função recursiva, até achar o 1º dia do feriado/final de semana
        $datasAnterires = array($dataAvaliacao);

        $diaAnterior = $this->subtrairDias($dataAvaliacao, 1);
        $naoUtil = $this->diaNaoUtil($diaAnterior);
        while ($naoUtil == true) {
            $datasAnterires[] = $diaAnterior;
            $diaAnterior = $this->subtrairDias($diaAnterior, 1);
            $naoUtil = $this->diaNaoUtil($diaAnterior);
        }
        
        return array_reverse($datasAnterires, false);
    }

    public function feriadosProximos($dataAvaliacao, $proximoUtil = false){
        //função recursiva, até achar o 1º dia do feriado/final de semana
        $proximosDias = array();
        $proximoDia = $this->somarDias($dataAvaliacao, 1);
        $naoUtil = $this->diaNaoUtil($proximoDia);

        while ($naoUtil == true) {
            $proximosDias[] = $proximoDia;
            $proximoDia = $this->somarDias($proximoDia, 1);
            $naoUtil = $this->diaNaoUtil($proximoDia);
        }

        if($this->diaNaoUtil($dataAvaliacao)){
            $proximosDias[] = $proximoDia;
        }
    
        
        return $proximosDias;

    }

    public function periodoNaoUtil($data){
        return array_merge($this->feriadosAnteriores($data), $this->feriadosProximos($data, true));
        
    }

    public function somarHoras($time1, $time2) {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time)
        {
            list($hour,$minute,$second) = explode(':', $time);
            $seconds += $hour*3600;
            $seconds += $minute*60;
            $seconds += $second;
        }
        $hours = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);
        $seconds -= $minutes*60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function diasUteisParaFimMes($ano = false, $mes = false, $diaAtual = false){
        if(!$ano){
            $ano = date('Y');
        }

        if(!$mes){
            $mes = date('m');
        }

        if(!$diaAtual){
            $diaAtual = date('d');
        }

        $fim = date("t", mktime(0,0,0,$mes,'01',$ano));
        $diasUteis = 0;
        for ($dia = $diaAtual; $dia <= $fim; $dia++) { 
            if(!$this->diaNaoUtil($ano.'-'.$mes.'-'.$dia)){
                $diasUteis++;
            }
        }
        return $diasUteis;
    }

}
?>