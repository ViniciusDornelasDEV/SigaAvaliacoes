<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Callcenter\Model;
use Application\Model\BaseTable;
use Zend\Db\Adapter\Adapter;

class Avaliacaocallcenter Extends BaseTable {

    //RECUPERAR AVALIAÇÔES COM PERÍODO EM ABERTO PARA OPERADOR CALLCENTER
	public function getAvaliacoesRespondidas($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = tb_callcenter.empresa', 
                array('nome_empresa', 'id_empresa' => 'id'));

            $select->join(
                array('u' => 'tb_usuario'), 
                'u.id = usuario', 
                array('nome_usuario' => 'nome'));


            $select->join(
                array('o' => 'tb_callcenter_operador'), 
                'o.id = u.callcenter', 
                array('nome_operador'),
                'LEFT'
                );

            $select->join(
                array('u2' => 'tb_usuario'),
                'o.usuario_diretor = u2.id',
                array('nome_gestor' => 'nome'),
                'LEFT'
                );



            $select->order('data_referencia DESC, e.nome_empresa, u.nome');
            if(isset($params['empresa']) && !empty($params['empresa'])){    
                $select->where(array('tb_callcenter.empresa' => $params['empresa']));
            }

            if(isset($params['usuario']) && !empty($params['usuario'])){    
                $select->where(array('tb_callcenter.usuario' => $params['usuario']));
            }

            if(isset($params['data_referencia']) && !empty($params['data_referencia'])){    
                $select->where(array(
                                'data_referencia' => $params['data_referencia']
                            ));
            }
            
            $select->where->notEqualTo('auditado', 'S');
            
        }); 
	}

    public function getAvaliacoesByMesEmpresa($empresa, $mes){
        return $this->getTableGateway()->select(function($select) use ($empresa, $mes) {
            $ultimoDia = date("t", mktime(0,0,0,$mes,'01',date('Y')));

            $select->where(array('tb_callcenter.empresa' => $empresa));
            $select->where->between('data_referencia', date('Y').'-'.$mes.'-'.'01', date('Y').'-'.$mes.'-'.$ultimoDia);
            
        }); 
    }

    public function getAvaliacoesAbertas($params){
        $adapter = $this->tableGateway->getAdapter();
        $where = '';
        if(!empty($params['empresa'])){
            //empresa
            $empresa = $adapter->platform->quoteIdentifier($params['empresa']);
            $where = ' AND e.id = '.$empresa;
        }
        $sql = 'SELECT co.*, e.nome_empresa, u2.nome AS nome_gestor
                FROM  tb_callcenter_operador AS co
                INNER JOIN tb_empresa AS e ON e.id = co.empresa 
                INNER JOIN tb_usuario AS u ON u.callcenter = co.id 
                INNER JOIN tb_usuario AS u2 ON u2.id = co.usuario_diretor
                WHERE e.ativo = "S" AND u.ativo = "S" AND co.ativo = "S" AND e.id NOT IN(
                        SELECT c.empresa
                        FROM tb_callcenter AS c 
                        WHERE c.data_referencia = "'.$params['data_referencia'].'"
                    )'.$where.' 
                ORDER BY e.nome_empresa, co.nome_operador;';

        $sql = str_replace('`', '', $sql);
        return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    public function getAvaliacacoById($idAvaliacao){
        return $this->getTableGateway()->select(function($select) use ($idAvaliacao) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = empresa', 
                array('nome_empresa'));

            $select->join(
                array('u' => 'tb_usuario'), 
                'u.id = usuario', 
                array('nome_usuario' => 'nome'));

            $select->where(array('tb_callcenter.id' => $idAvaliacao));
        })->current(); 
    }

    public function getAvaliacoesByParams($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            if(isset($params['empresa']) && !empty($params['empresa'])){
                $select->where(array('tb_callcenter.empresa' => $params['empresa']));
            }

            $select->where->notEqualTo('auditado', 'S');

            $select->where('tb_callcenter.data_referencia >= "'.$params['inicio'].' 00:00:00"');
            $select->where('tb_callcenter.data_referencia <= "'.$params['fim'].' 23:59:59"'); 
            
        }); 
    }

    public function getAvaliacoesGraficoPersonalizado($empresas, $campos, $datas){
        return $this->getTableGateway()->select(function($select) use ($empresas, $campos, $datas) {
            $select->columns($campos);
            
            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = empresa',
                    array('nome_empresa', 'id_empresa' => 'id')
                );

            $select->where->in('empresa', $empresas);
            $select->where->notEqualTo('auditado', 'S');
            $select->where('tb_callcenter.data_referencia >= "'.$datas['inicio'].' 00:00:00"');
            $select->where('tb_callcenter.data_referencia <= "'.$datas['fim'].' 23:59:59"'); 
            $select->order('nome_empresa');
            
        });
    }

    public function getAvaliacoesToExcell($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                array('e' => 'tb_empresa'), 
                'e.id = tb_callcenter.empresa', 
                array('nome_empresa', 'id_empresa' => 'id'));

            $select->join(
                array('u' => 'tb_usuario'), 
                'u.id = usuario', 
                array('nome_usuario' => 'nome'));

            $select->join(
                array('o' => 'tb_callcenter_operador'), 
                'o.id = u.callcenter', 
                array('nome_operador'),
                'LEFT');

            $select->join(
                array('c' => 'tb_cidade'), 
                'c.id = e.cidade', 
                array('nome_cidade' => 'nome'),
                'LEFT');

            $select->join(
                array('es' => 'tb_estado'), 
                'es.id = c.estado', 
                array('nome_estado' => 'nome', 'id_estado' => 'id'),
                'LEFT');

            $select->order('es.uf, c.nome, e.nome_empresa, data_referencia, u.nome');
            if(isset($params['empresa']) && !empty($params['empresa'])){    
                $select->where(array('tb_callcenter.empresa' => $params['empresa']));
            }

            $select->where('tb_callcenter.data_referencia >= "'.$params['inicio'].' 00:00:00"');
            $select->where('tb_callcenter.data_referencia <= "'.$params['fim'].' 23:59:59"'); 
            $select->where->notEqualTo('auditado', 'S');
        }); 
    }

    public function getAvaliacoesToExcell2($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                array('e' => 'tb_empresa'),
                'e.id = tb_callcenter.empresa', 
                array('nome_empresa', 'id_empresa' => 'id'));

            $select->join(
                array('u' => 'tb_usuario'), 
                'u.id = usuario', 
                array('nome_usuario' => 'nome'));

            $select->join(
                array('o' => 'tb_callcenter_operador'), 
                'o.id = u.callcenter', 
                array('nome_operador'),
                'LEFT');

            $select->order('data_referencia, e.nome_empresa');
            if(isset($params['empresa']) && !empty($params['empresa'])){    
                $select->where(array('tb_callcenter.empresa' => $params['empresa']));
            }

            $select->where->notEqualTo('auditado', 'S');
            $select->where('tb_callcenter.data_referencia >= "'.$params['inicio'].' 00:00:00"');
            $select->where('tb_callcenter.data_referencia <= "'.$params['fim'].' 23:59:59"'); 
                
            
        }); 
    }

    public function criarAvaliacaoAuditada($dados, $idAvaliacao){
        //criar transaction
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //update para auditado = S
            parent::update(array('auditado' => 'S'), array('id' => $idAvaliacao));
            
            //inserir nova avaliação auditada
            $idAvaliacao = parent::insert($dados);
            $connection->commit();
            return $idAvaliacao;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }

        return false;
    }

}
