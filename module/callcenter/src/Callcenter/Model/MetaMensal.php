<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Callcenter\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class MetaMensal Extends BaseTable {

    public function getMetas(){
        return $this->getTableGateway()->select(function($select) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa')
            );

            $select->order('ano DESC, mes DESC, nome_empresa');
     
        });
    }

    public function replicarMetas($dados){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //pesquisar metas  mensais do mes de origem
            $metasOrigem = $this->getRecordsFromArray(array('ano' => $dados['ano_origem'], 'mes' => $dados['mes_origem']));

            //percorrer metas do mes de origem
            foreach ($metasOrigem as $meta) {
                //inserir metas de origem para data de destino
                $this->insert(array(
                        'mes' => $dados['mes_destino'], 
                        'ano' => $dados['ano_destino'], 
                        'valor' => $meta->valor, 
                        'empresa' => $meta->empresa
                    ));
            }
            
            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
    }
}
