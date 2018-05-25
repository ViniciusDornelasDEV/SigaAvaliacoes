<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Avaliacao\Model;

use Avaliacao\Model\Base;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;

class Agendamento Extends Base {

    public function getAvaliacoesRespondidasByEmpresa(array $params){
        $adapter = $this->tableGateway->getAdapter();
        $where = ' AND id_formulario IS NULL ';

        if(isset($params['auditado'])){
            $where .=  'AND auditado = '.$adapter->platform->quoteIdentifier($params['auditado']);
        }

        if(isset($params['empresas'])){
            $where .= 'AND (';
            foreach ($params['empresas'] as $empresa) {
                $where .= 'empresa = '.$adapter->platform->quoteIdentifier($empresa['empresa']).' OR ';
            }
            $where = substr($where, 0, -3);
            $where .= ')';
        }

        if(isset($params['ano'])){
            $where .= ' AND ano = '.$adapter->platform->quoteIdentifier($params['ano']);
        }

        if(isset($params['mes'])){
            $where .= ' AND mes = '.$adapter->platform->quoteIdentifier($params['mes']);
        }

        $sql = '(SELECT tb_agendamento.ano AS ano, tb_agendamento.mes AS mes, tb_agendamento.empresa AS empresa, nome_empresa FROM tb_agendamento 
                    INNER JOIN tb_empresa AS e ON e.id = empresa
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_comercial.ano AS ano, tb_comercial.mes AS mes, tb_comercial.empresa AS empresa, nome_empresa FROM tb_comercial 
                    INNER JOIN tb_empresa AS e ON e.id = empresa
                    WHERE 1 = 1 '.$where.') 
                UNION
                (SELECT tb_processo.ano AS ano, tb_processo.mes AS mes, tb_processo.empresa AS empresa, nome_empresa FROM tb_processo 
                    INNER JOIN tb_empresa AS e ON e.id = empresa
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_qualidade.ano AS ano, tb_qualidade.mes AS mes, tb_qualidade.empresa AS empresa, nome_empresa FROM tb_qualidade 
                    INNER JOIN tb_empresa AS e ON e.id = empresa
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_seguranca.ano AS ano, tb_seguranca.mes AS mes, tb_seguranca.empresa AS empresa, nome_empresa FROM tb_seguranca 
                    INNER JOIN tb_empresa AS e ON e.id = empresa
                    WHERE 1 = 1 '.$where.')  
                ORDER BY ano, mes, empresa';
        $sql = str_replace('`', '', $sql);
        $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }

    public function getAvaliacoesRespondidasByEmpresas(array $params){
        $adapter = $this->tableGateway->getAdapter();
        $where = ' AND id_formulario IS NULL ';

        if(isset($params['auditado'])){
            $where .=  'AND auditado = '.$adapter->platform->quoteIdentifier($params['auditado']);
        }

        if(isset($params['tb_medico_avaliacao_qualitativa.empresa'])){
            $where .= 'AND empresa = '.$adapter->platform->quoteIdentifier($params['tb_medico_avaliacao_qualitativa.empresa']);
        }

        if(isset($params['ano'])){
            $where .= ' AND ano = '.$adapter->platform->quoteIdentifier($params['ano']);
        }

        if(isset($params['mes'])){
            $where .= ' AND mes = '.$adapter->platform->quoteIdentifier($params['mes']);
        }

        $sql = '(SELECT tb_agendamento.ano AS ano, tb_agendamento.mes AS mes, tb_agendamento.empresa AS empresa, e.nome_empresa 
                    FROM tb_agendamento 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    WHERE 1 = 1 '.$where.') 
                UNION
                (SELECT tb_comercial.ano AS ano, tb_comercial.mes AS mes, tb_comercial.empresa AS empresa, e.nome_empresa 
                    FROM tb_comercial 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_processo.ano AS ano, tb_processo.mes AS mes, tb_processo.empresa AS empresa, e.nome_empresa 
                    FROM tb_processo 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_qualidade.ano AS ano, tb_qualidade.mes AS mes, tb_qualidade.empresa AS empresa, e.nome_empresa 
                    FROM tb_qualidade 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_seguranca.ano AS ano, tb_seguranca.mes AS mes, tb_seguranca.empresa AS empresa, e.nome_empresa  
                    FROM tb_seguranca 
                    INNER JOIN tb_empresa AS e ON e.id = empresa
                    WHERE 1 = 1 '.$where.')   
                ORDER BY ano, mes, empresa, nome_empresa;';

        $sql = str_replace('`', '', $sql);

        $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }

    public function getAvaliacoesRespondidasAuxiliarByEmpresas(array $params){
        $adapter = $this->tableGateway->getAdapter();
        $where = ' AND id_formulario IS NULL ';

        if(isset($params['auditado'])){
            $where .=  'AND auditado = '.$adapter->platform->quoteIdentifier($params['auditado']);
        }

        if(isset($params['tb_medico_avaliacao_qualitativa.empresa'])){
            $where .= 'AND ae.empresa = '.$adapter->platform->quoteIdentifier($params['tb_medico_avaliacao_qualitativa.empresa']);
        }

        if(isset($params['ano'])){
            $where .= ' AND ano = '.$adapter->platform->quoteIdentifier($params['ano']);
        }

        if(isset($params['mes'])){
            $where .= ' AND mes = '.$adapter->platform->quoteIdentifier($params['mes']);
        }

        $where .= ' AND ae.usuario = '.$adapter->platform->quoteIdentifier($params['ae.usuario']);

        $sql = '(SELECT tb_agendamento.ano AS ano, tb_agendamento.mes AS mes, tb_agendamento.empresa AS empresa, e.nome_empresa 
                    FROM tb_agendamento 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    INNER JOIN tb_usuario_auxiliar_empresas AS ae ON ae.empresa = e.id
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_comercial.ano AS ano, tb_comercial.mes AS mes, tb_comercial.empresa AS empresa, e.nome_empresa 
                    FROM tb_comercial 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    INNER JOIN tb_usuario_auxiliar_empresas AS ae ON ae.empresa = e.id
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_processo.ano AS ano, tb_processo.mes AS mes, tb_processo.empresa AS empresa, e.nome_empresa 
                    FROM tb_processo 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    INNER JOIN tb_usuario_auxiliar_empresas AS ae ON ae.empresa = e.id
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_qualidade.ano AS ano, tb_qualidade.mes AS mes, tb_qualidade.empresa AS empresa, e.nome_empresa 
                    FROM tb_qualidade 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    INNER JOIN tb_usuario_auxiliar_empresas AS ae ON ae.empresa = e.id
                    WHERE 1 = 1 '.$where.') 
                UNION 
                (SELECT tb_seguranca.ano AS ano, tb_seguranca.mes AS mes, tb_seguranca.empresa AS empresa, e.nome_empresa  
                    FROM tb_seguranca 
                    INNER JOIN tb_empresa AS e ON e.id = empresa 
                    INNER JOIN tb_usuario_auxiliar_empresas AS ae ON ae.empresa = e.id
                    WHERE 1 = 1 '.$where.') 
                ORDER BY ano, mes, empresa, nome_empresa;';

        $sql = str_replace('`', '', $sql);

        $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }


}