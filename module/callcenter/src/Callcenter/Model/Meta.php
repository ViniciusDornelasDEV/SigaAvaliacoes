<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Callcenter\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class Meta Extends BaseTable {

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
}
