<?php
namespace Application\Model;

use Application\Model\BaseTable;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;

class PilhaAvaliacao Extends BaseTable {

    //Aba C = Clínica, M = Médico
    public function getAvaliacaoAbertaByDate($date, $aba = 'C', $mes = false, $ano = false){
        return $this->getTableGateway()->select(function($select) use ($date, $aba, $mes, $ano) {
            $mes = (int) $mes;
        	$select->where('"'.$date.'" >= inicio');
			$select->where('"'.$date.'" <= termino');

            if($mes && $ano){
                $select->where(array('mes_referencia' => $mes, 'ano_referencia' => $ano));
            }
            $select->where(array('aba' => $aba));
			$select->order('ano_referencia DESC, mes_referencia DESC');
            
       });
    }

    public function getAvaliacoesByDate($date, $empresa, $aba = 'M'){
            $sql = 'SELECT tb_pilha_avaliacoes.*, a.id AS id_agendamento, c.id AS id_comercial, p.id AS id_processo, q.id AS id_qualidade, s.id AS id_seguranca, at.id AS id_ata 
                    FROM tb_pilha_avaliacoes ';
            
            //join de todas as tabelas
            $sql .= $this->join('a', 'agendamento', $empresa);
            $sql .= $this->join('c', 'comercial', $empresa);
            $sql .= $this->join('p', 'processo', $empresa);
            $sql .= $this->join('q', 'qualidade', $empresa);
            $sql .= $this->join('s', 'seguranca', $empresa);
            $sql .= $this->join('at', 'ata', $empresa);
            $sql .= 'WHERE "'.$date.'" >= inicio AND "'.$date.'" <= termino AND tb_pilha_avaliacoes.aba = "'.$aba.'"';
            $sql .= 'GROUP BY id, inicio, termino, mes_referencia, ano_referencia ';
            $sql .= 'ORDER BY ano_referencia DESC, mes_referencia DESC;';

            $adapter = $this->tableGateway->getAdapter();
            $sql = str_replace('`', '', $sql);
            
            return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    private function join($prefixo, $tabela, $empresa){
        if(isset($empresa['empresas'])){
            $empresas = '';
            foreach ($empresa['empresas'] as $empresaAbas) {
                $empresas .= $empresaAbas['empresa'].', ';
            }
            $empresas = substr($empresas, 0, -2);
            $whereString = $prefixo.'.mes = mes_referencia AND '.$prefixo.'.ano = ano_referencia AND ('.$prefixo.'.empresa IN('.$empresas.') OR '.$prefixo.'.empresa IS NULL)';
        }else{
            $whereString = $prefixo.'.mes = mes_referencia AND '.$prefixo.'.ano = ano_referencia AND ('.$prefixo.'.empresa = '.$empresa.' OR '.$prefixo.'.empresa IS NULL)';
        }
        $sql = 'LEFT JOIN tb_'.$tabela.' AS '.$prefixo.' ON '.$whereString;      
        return $sql;   
    }

}
?>