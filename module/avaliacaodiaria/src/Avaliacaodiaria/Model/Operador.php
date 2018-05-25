<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Avaliacaodiaria\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class Operador Extends BaseTable {

    public function getOperadoresByParams($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa'));

            $select->order('nome_empresa, nome_operador');
            $select->where($params);
        }); 
    }


    public function getAvaliacoesNaoRespondidas($data){
        

        $adapter = $this->tableGateway->getAdapter();
        $data = $adapter->platform->quoteIdentifier($data);
        
        $sql = 'SELECT co.*, c.*, pa.data_referencia, e.nome_empresa
                FROM tb_avaliacaodiaria_operador AS co 
                INNER JOIN tb_usuario AS u ON u.avaliacao_diaria = co.id
                INNER JOIN tb_empresa AS e ON e.id = co.empresa 
                LEFT JOIN tb_avaliacaodiaria AS c ON c.usuario = u.id
                INNER JOIN tb_pilha_avaliacoes_avaliacaodiaria AS pa ON "'.$data.'" BETWEEN pa.inicio AND pa.termino
                WHERE c.usuario IS NULL AND co.ativo ="S"
                ORDER BY co.nome_operador;';
        $sql = str_replace('`', '', $sql);

        return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
    }

    public function insert($dados){
        //criar transaction
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //inserir operador
            $idOperador = parent::insert($dados);

            //inserir usuário
            $tbUsuario = new TableGateway('tb_usuario', $adapter);
            $dadosUsuario = array(
                    'nome'              => $dados['nome_operador'],
                    'login'             => $dados['login'],
                    'senha'             => $dados['senha'],
                    'id_usuario_tipo'   => 13,
                    'avaliacao_diaria'  => $idOperador,
                );
            $tbUsuario->insert($dadosUsuario);
            $connection->commit();
            return $idOperador;
        } catch (Exception $e) {
            $connection->rollback();
        }


        
    }
}
