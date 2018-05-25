<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class Medico Extends BaseTable {

    public function getMedicosByDiretor($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa'));

            $select->order('nome_empresa, nome_medico');

            if(isset($params['usuario_diretor'])){
                $select->where
                   ->nest
                       ->equalTo('usuario_diretor', $params['usuario_diretor'])
                       ->or
                       ->equalTo('usuario_diretor2', $params['usuario_diretor'])
                   ->unnest;
            }
        }); 
    }

    public function insert($dados){
        //criar transaction
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //inserir médico
            $idMedico = parent::insert($dados);

            //inserir usuário
            if(isset($dados['login']) && !empty($dados['login'])){
                $tbUsuario = new TableGateway('tb_usuario', $adapter);
                $dadosUsuario = array(
                        'nome'              => $dados['nome_medico'],
                        'login'             => $dados['login'],
                        'senha'             => $dados['senha'],
                        'id_usuario_tipo'   => 9,
                        'medico'            => $idMedico,
                    );
                $tbUsuario->insert($dadosUsuario);
            }
            $connection->commit();
            return $idMedico;
        } catch (Exception $e) {
            $connection->rollback();
        }  
    }

    public function ativar($medico){
        //criar transaction
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $medico = $this->getRecord($medico);
            $this->update(array('ativo' => 'S'), array('id' => $medico['id']));

            //inserir usuário
            $tbUsuario = new TableGateway('tb_usuario', $adapter);
            $tbUsuario->update(array('ativo' => 'S'), array('medico' => $medico['id']));
            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }  
        return false;
    }


}
