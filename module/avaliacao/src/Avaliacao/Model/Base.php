<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Avaliacao\Model;

use Application\Model\BaseTable;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;

class Base Extends BaseTable {
    public function getavaliacoesAuditar($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = empresa',
                    array('nome_empresa')
                );
            $select->where($params);

            $select->order('ano, mes, nome_empresa');
            
        }); 
    }

    public function getavaliacoesNaoRespondidas($ano, $mes, $aba, $empresa = false){
        $adapter = $this->tableGateway->getAdapter();
        $nomeTabela = $this->getTableGateway()->getTable();
        
        $where = '';
        if($empresa){
            $where = ' AND e.id = '.$adapter->platform->quoteIdentifier($empresa);
        }

        $mes = $adapter->platform->quoteIdentifier($mes);
        $ano = $adapter->platform->quoteIdentifier($ano);
        $aba = $adapter->platform->quoteIdentifier($aba);

        $sql = 'SELECT e.id AS id_empresa, e.nome_empresa, a.*
        FROM tb_empresa as e 
        INNER JOIN tb_abas_usuario AS au ON au.empresa = e.id AND au.aba = '.$aba.'
        LEFT JOIN '.$nomeTabela.' as a ON a.empresa = e.id AND mes = "'.$mes.'" AND ano = "'.$ano.'"
        WHERE e.ativo = "S" AND a.id IS NULL'.$where.'
        GROUP BY e.id
        ORDER BY e.nome_empresa;'; 

        $sql = str_replace('`', '', $sql);

        $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
            
    }

    public function getavaliacaoByParams($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            
            $select->where(array('ano' => $params['ano'], 'mes' => $params['mes'], 'empresa' => $params['empresa']));

            $select->order('id DESC');
            
        }); 
    }

    public function insertAudicao(array $dados, $idAgendamento, $usuario){
        $connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try{
            //alterar o formulário para auditado
            $this->update(array('auditor' => $usuario, 'auditado' => 'S'), array('id' => $idAgendamento));
            
            $avaliacao = $this->getRecord($idAgendamento);

            //Tratar dados para avaliação auditada
            $dados['id_formulario'] = $avaliacao['id'];
            $dados['auditado'] = 'I';
            $dados['auditor'] = $usuario;
            unset($dados['enviar']);
            $dados['ano'] = $avaliacao['ano'];
            $dados['mes'] = $avaliacao['mes'];
            $dados['usuario'] = $avaliacao['usuario'];
            $dados['empresa'] = $avaliacao['empresa'];
            if(isset($avaliacao->medico)){
                $dados['medico'] = $avaliacao['medico'];
                $dados['finalizada'] = 'S';
            }

            //inserir tupla avaliação auditada
            $this->insert($dados);

            $connection->commit();
            return true;
        }catch(Exception $e){
            $connection->rollback();
            return false;
        }
    }
}
?>