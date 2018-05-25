<?php


namespace Avaliacao\Model;

use Application\Model\BaseTable;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;

class Campoempresa Extends BaseTable {
    public function getCamposEmpresaByAba($aba, $empresa){
        return $this->getTableGateway()->select(function($select) use ($aba, $empresa) {

            $select->join(
                    array('cc' => 'tb_campo_categoria'),
                    'cc.id = tb_campo_empresa.categoria_questao',
                    array('nome_categoria' => 'nome')
                );

            $select->join(
                    array('c' => 'tb_campo'),
                    'c.id = tb_campo_empresa.campo',
                    array('nome_campo', 'tooltip')
                );

            $select->where(array('tb_campo_empresa.aba' => $aba, 'empresa' => $empresa));
            $select->order('c.ordem');
        }); 
    }

    public function personalizarFormEmpresa($campos, $dados){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        
        try {
            //percorrer campos
            foreach ($campos as $campo) {
                //inserir campos para a empresa cadastrada
                $dadosUpdate = array(
                        'categoria_questao' => $dados[$campo['nome_campo'].'categoria'],
                        'aparecer'      => $dados[$campo['nome_campo'].'aparecer'],
                        'obrigatorio'   => $dados[$campo['nome_campo'].'obrigatorio'],
                        'label'         => $dados[$campo['nome_campo'].'label']
                    );
                $where = array(
                            'aba'       => $campo['aba'],
                            'campo'     => $campo['campo'],
                            'empresa'   => $campo['empresa'],
                        );

                $this->update($dadosUpdate, $where);
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
