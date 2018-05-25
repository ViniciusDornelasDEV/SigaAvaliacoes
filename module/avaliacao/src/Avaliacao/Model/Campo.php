<?php


namespace Avaliacao\Model;

use Application\Model\BaseTable;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;

class Campo Extends BaseTable {
    public function getCamposByAba($aba){
        return $this->getTableGateway()->select(function($select) use ($aba) {
            $select->join(
                    array('a' => 'tb_abas'),
                    'a.id = tb_campo.aba',
                    array('nome_aba' => 'nome')
                );

            $select->join(
                    array('cc' => 'tb_campo_categoria'),
                    'cc.id = categoria_questao',
                    array('nome_categoria' => 'nome')
                );

            $select->where(array('tb_campo.aba' => $aba));
        }); 
    }


}
