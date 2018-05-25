<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;
use Application\Model\BaseTable;

class AuxiliarEmpresas Extends BaseTable {

    public function getEmpresasByUsuario($idUsuario){
        return $this->getTableGateway()->select(function($select) use ($idUsuario) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa')
            );
        
            $select->where(array('usuario' => $idUsuario));
            $select->order('nome_empresa');

        }); 
    }

    public function insert($dados, $empresas = false){
        if($dados['empresa'] == 'todas'){
            $connection = $this->getTableGateway()->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();
            $this->delete(array('usuario' => $dados['usuario']));
            try{
                foreach ($empresas as $empresa) {
                    $dados = array(
                                'usuario'  => $dados['usuario'],
                                'empresa'  => $empresa->id
                            );  

                    parent::insert($dados);
                }
            }catch(Exception $e){    
                $connection->rollback();
                return false;
            }

            $connection->commit();
                        
            return true;
        }else{
            return parent::insert($dados);
        }

    }

}
