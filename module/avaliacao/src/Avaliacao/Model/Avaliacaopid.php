<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Avaliacao\Model;

use Avaliacao\Model\Base;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

class Avaliacaopid Extends Base {


    //Pesquisar todas as avaliações do diretor em uma empresa
    public function getAvaliacoesRespondidasByEmpresa($idEmpresa, $idDiretor, $mesReferencia, $anoReferencia){
        $adapter = $this->tableGateway->getAdapter();
        
        $idEmpresa = $adapter->platform->quoteIdentifier($idEmpresa);
        $idDiretor = $adapter->platform->quoteIdentifier($idDiretor);
        $mesReferencia = $adapter->platform->quoteIdentifier($mesReferencia);
        $anoReferencia = $adapter->platform->quoteIdentifier($anoReferencia);
        
        $sql = 'SELECT m.id AS id_medico, aq.id AS id_avaliacao
                FROM tb_medicos AS m
                LEFT JOIN tb_medico_avaliacao_pid AS aq ON aq.medico = m.id AND aq.empresa = m.empresa AND
                    aq.mes = "'.$mesReferencia.'" AND aq.ano = "'.$anoReferencia.'" AND 
                    (aq.usuario = '.$idDiretor.' AND aq.finalizada = "N" OR aq.usuario IS NULL)
                WHERE m.empresa = '.$idEmpresa.' AND m.usuario_diretor = '.$idDiretor.';';
        

        $sql = str_replace('`', '', $sql);

        return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    public function getavaliacoesAuditar($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_medico_avaliacao_pid.empresa',
                    array('nome_empresa')
                );

            $select->join(
                    array('m' => 'tb_medicos'),
                    'm.id = tb_medico_avaliacao_pid.medico',
                    array('nome_medico')
                );

            if(isset($params['empresa'])){
                //colocar nome da tabela no parâmetro empresa
                $params['tb_medico_avaliacao_pid.empresa'] = $params['empresa'];
                unset($params['empresa']);
            }

            $select->where($params);

            $select->order('ano, mes, nome_empresa, nome_medico');
            
        }); 
    }
}
?>