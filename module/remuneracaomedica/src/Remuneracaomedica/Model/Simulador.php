<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Remuneracaomedica\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class Simulador Extends BaseTable {

    public function getIndicadores($idMedico){
        return $this->getTableGateway()->select(function($select) use ($idMedico) {
            $select->where(array('medico' => $idMedico));

            $select->order('ano ASC, mes ASC');
     
        });
    }


}
