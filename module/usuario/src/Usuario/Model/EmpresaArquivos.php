<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;
use Application\Model\BaseTable;

use Zend\Db\TableGateway\TableGateway;

class EmpresaArquivos Extends BaseTable {

    public function replicarArquivos($arquivo){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            //pesquisar empresas
            $tbEmpresa = new TableGateway('tb_empresa', $adapter);
            $empresas = $tbEmpresa->select();

            $arquivosReplicados = 0;

            $arquivos = $this->fetchAll()->toArray();
            //percorrer empresas
            foreach ($empresas as $empresa) {
                if($empresa->id != $arquivo->empresa){
                    $encontrado = false;
                    foreach ($arquivos as $arquivoAlterar) {
                        if($arquivoAlterar['empresa'] == $empresa->id && $arquivoAlterar['nome'] == $arquivo->nome){
                            $encontrado = true;
                            $this->update(array('arquivo' => $arquivo->arquivo), 
                                          array('empresa' => $empresa->id, 'nome' => $arquivo->nome)
                                    );
                        }
                    }

                    if(!$encontrado){
                        $this->insert(array('nome' => $arquivo->nome, 'arquivo' => $arquivo->arquivo, 'empresa' => $empresa->id));
                    }
                    $arquivosReplicados++;
                }
            }

            //commit
            $connection->commit();

            return $arquivosReplicados;
        } catch (Exception $e) {
            $connection->rollback();
        }
        return false;
    }
}
