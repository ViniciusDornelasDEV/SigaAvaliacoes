<?php

namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Funcoes extends AbstractPlugin
{
    public function numeroToMes($mes){
        switch ($mes) {
            case '01':
                return 'Jan';
            case '02':
                return 'Fev';
            case '03':
                return 'Mar';
            case '04':
                return 'Abr';
            case '05':
                return 'Maio';
            case '06':
                return 'Jun';
            case '07':
                return 'Jul';
            case '08':
                return 'Ago';
            case '09':
                return 'Set';
            case '10':
                return 'Out';
            case '11':
                return 'Nov';
            case '12':
                return 'Dez';
            default:
                return false;
                break;
        }
    }

    public function diaToSemana($date){
        $dia = date('w', strtotime($date));
        $semana = array(
            '0' => 'Dom', 
            '1' => 'Seg',
            '2' => 'Ter',
            '3' => 'Qua',
            '4' => 'Qui',
            '5' => 'Sex',
            '6' => 'Sáb'
        );
        return $semana[$dia];
    }

    public function converterData($data) {
        if(!empty($data)){
            if(strpos($data, ' ')){
                return self::ConverteTimestamp($data);
            }else{
                return self::ConverteData($data);
            }
         }
    }
    
    private function ConverteData($data){
        @$TipoData = stristr($data, "/");
        if($TipoData != false){
            $Texto = explode("/",$data);
            return $Texto[2]."-".$Texto[1]."-".$Texto[0];
        }else{
            $Texto = explode("-",$data);
            return $Texto[2]."/".$Texto[1]."/".$Texto[0];
         }
    }
    
    private function ConverteTimestamp($data){
        $Dados = explode(" ", $data);
        return self::ConverteData($Dados[0]).' '.$Dados[1];
    }
}