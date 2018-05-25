<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Avaliacaodiaria\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class Graficopersonalizadosub Extends BaseTable {

    public function getEmpresas(){
        return $this->getTableGateway()->select(function($select) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa', 'id_empresa' => 'id')
            );

            $select->order('nome_empresa');      
        }); 

    }

    public function getCampos(){
        return $this->getTableGateway()->select(function($select) {
            $select->join(
                array('c' => 'tb_campo'), 
                'c.id = campo', 
                array('label', 'nome_campo')
            );
     
        });
    }

    public function inserir($empresas){
            $adapter = $this->getTableGateway()->getAdapter();
            $connection = $adapter->getDriver()->getConnection();
            $connection->beginTransaction();

            
            try {
                foreach ($empresas as $empresa) {
                    //inserir todas as empresas no gráfico
                    $this->insert(array('empresa' => $empresa->id));
                }

                //commit
                $connection->commit();

                return true;
            } catch (Exception $e) {
                $connection->rollback();
            }
        return false;
    }
}
