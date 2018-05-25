<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;
use Application\Model\BaseTable;

class AbasUsuario Extends BaseTable {

    public function getAbasByUsuario($idUsuario){
        return $this->getTableGateway()->select(function($select) use ($idUsuario) {
            $select->join(
                array('a' => 'tb_abas'), 
                'a.id = aba', 
                array('nome_aba' => 'nome')
            );

            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa')
            );
        
            $select->where(array('usuario' => $idUsuario));
            $select->order('nome_empresa, a.nome');

        }); 
    }

    public function getAbasByEmpresa($idEmpresa){
        return $this->getTableGateway()->select(function($select) use ($idEmpresa) {
            $select->columns(array('aba'));
            $select->join(
                array('u' => 'tb_usuario'), 
                'u.id = usuario', 
                array()
            );
            $select->where(array('tb_abas_usuario.empresa' => $idEmpresa));
            $select->group(array('aba'));
        });   

    }

    public function getEmpresasUsuario($idUsuario){
        return $this->getTableGateway()->select(function($select) use ($idUsuario) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa')
            );
        
            $select->where(array('usuario' => $idUsuario));
            $select->group('nome_empresa');
            $select->order('nome_empresa');

        }); 
    }
}
