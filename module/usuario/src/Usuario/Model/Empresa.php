<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class Empresa Extends BaseTable {

    public function getEmpresasByParams($params = false){
        return $this->getTableGateway()->select(function($select) use ($params) {
            if($params){
                if($params['nome_empresa']){
                    $select->where->like('nome_empresa', '%'.$params['nome_empresa'].'%');
                }

                if($params['nome_responsavel']){
                    $select->where->like('nome_responsavel', '%'.$params['nome_responsavel'].'%');
                }

                if($params['email']){
                    $select->where->like('email', '%'.$params['email'].'%');
                }

                if($params['ativo']){
                    $select->where(array('ativo' => $params['ativo']));
                }

            }

            $select->order('nome_empresa');
            
        }); 
    }

    public function insert($dados){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            //inserir empresa
            $idEmpresa = parent::insert($dados);
            
            //Pesquisar campos
            $tbCampo = new TableGateway('tb_campo', $adapter);
            $campos = $tbCampo->select();

            $tbCampoEmpresa = new TableGateway('tb_campo_empresa', $adapter);
            //percorrer campos
            foreach ($campos as $campo) {
                //inserir campos para a empresa cadastrada
                $dadosInserir = array(
                        'aba'       => $campo->aba,
                        'campo'     => $campo->id,
                        'empresa'   => $idEmpresa,
                        'categoria_questao' => $campo->categoria_questao,
                        'aparecer'      => 'S',
                        'obrigatorio'   => 'S',
                        'label'         => $campo->label
                    );
                $tbCampoEmpresa->insert($dadosInserir);
            }

            //commit
            $connection->commit();

            return $idEmpresa;
        } catch (Exception $e) {
            $connection->rollback();
        }
        return false;
    }
}
