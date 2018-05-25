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
use Zend\Db\Adapter\Adapter;

class Avaliacaoqualitativa Extends Base {

    //Pesquisar todas as avaliações do diretor em uma empresa
    public function getAvaliacoesRespondidasByEmpresa($idEmpresa, $idDiretor, $mesReferencia, $anoReferencia){
        $adapter = $this->tableGateway->getAdapter();
        
        $idEmpresa = $adapter->platform->quoteIdentifier($idEmpresa);
        $idDiretor = $adapter->platform->quoteIdentifier($idDiretor);
        $mesReferencia = $adapter->platform->quoteIdentifier($mesReferencia);
        $anoReferencia = $adapter->platform->quoteIdentifier($anoReferencia);
        
        $sql = 'SELECT m.id AS id_medico, aq.id AS id_avaliacao
                FROM tb_medicos AS m
                LEFT JOIN tb_medico_avaliacao_qualitativa AS aq ON aq.medico = m.id AND aq.empresa = m.empresa AND
                    aq.mes = "'.$mesReferencia.'" AND aq.ano = "'.$anoReferencia.'" AND 
                    (aq.usuario = '.$idDiretor.' AND aq.finalizada = "N" OR aq.usuario IS NULL)
                WHERE m.empresa = '.$idEmpresa.' AND m.usuario_diretor = '.$idDiretor.';';
        

        $sql = str_replace('`', '', $sql);

        return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    public function getAvaliacoesRespondidasByPeriodo($idDiretor, $mesReferencia, $anoReferencia, $params){
        $adapter = $this->tableGateway->getAdapter();

        $idDiretor = $adapter->platform->quoteIdentifier($idDiretor);
        $mesReferencia = $adapter->platform->quoteIdentifier($mesReferencia);
        $anoReferencia = $adapter->platform->quoteIdentifier($anoReferencia);

        if($params['respondida'] == 'N'){
            $sql = 'SELECT e.nome_empresa, m.nome_medico, m.id AS id_medico, aq.finalizada
                    FROM tb_medicos AS m 
                    INNER JOIN tb_empresa AS e ON e.id = m.empresa
                    LEFT JOIN tb_medico_avaliacao_qualitativa AS aq ON m.id = aq.medico AND aq.empresa = m.empresa 
                        AND aq.mes = "'.$mesReferencia.'" AND aq.ano = "'.$anoReferencia.'"
                    WHERE aq.medico IS NULL AND (m.usuario_diretor = '.$idDiretor.' OR m.usuario_diretor2 = '.$idDiretor.') AND m.ativo = "S"
                    ORDER BY m.nome_medico';
        }else{
            if($params['respondida'] == 'Q'){
                $finalizada = 'N';
            }else{
                $finalizada = 'S';
            }
            $sql = 'SELECT e.nome_empresa, m.nome_medico, m.id AS id_medico, aq.finalizada 
                    FROM tb_medicos AS m 
                    INNER JOIN tb_empresa AS e ON e.id = m.empresa
                    INNER JOIN tb_medico_avaliacao_qualitativa AS aq ON aq.medico = m.id AND aq.empresa = m.empresa AND
                        aq.mes = "'.$mesReferencia.'" AND aq.ano = "'.$anoReferencia.'" AND 
                         aq.finalizada = "'.$finalizada.'"
                    WHERE (m.usuario_diretor = '.$idDiretor.' OR m.usuario_diretor2 = '.$idDiretor.') AND m.ativo = "S"
                    ORDER BY m.nome_medico';

        }

        if(empty($params['respondida'])){
            $sql = '(SELECT e.nome_empresa, m.nome_medico, m.id AS id_medico, aq.finalizada
                    FROM tb_medicos AS m 
                    INNER JOIN tb_empresa AS e ON e.id = m.empresa
                    LEFT JOIN tb_medico_avaliacao_qualitativa AS aq ON m.id = aq.medico AND aq.empresa = m.empresa 
                        AND aq.mes = "'.$mesReferencia.'" AND aq.ano = "'.$anoReferencia.'"
                    WHERE aq.medico IS NULL AND (m.usuario_diretor = '.$idDiretor.' OR m.usuario_diretor2 = '.$idDiretor.') AND m.ativo = "S"
                    ORDER BY m.nome_medico, aq.finalizada)
                    UNION
                    (SELECT e.nome_empresa, m.nome_medico, m.id AS id_medico, aq.finalizada 
                    FROM tb_medicos AS m 
                    INNER JOIN tb_empresa AS e ON e.id = m.empresa
                    INNER JOIN tb_medico_avaliacao_qualitativa AS aq ON aq.medico = m.id AND aq.empresa = m.empresa AND
                        aq.mes = "'.$mesReferencia.'" AND aq.ano = "'.$anoReferencia.'"
                    WHERE (m.usuario_diretor = '.$idDiretor.' OR m.usuario_diretor2 = '.$idDiretor.') AND m.ativo = "S"
                    ORDER BY m.nome_medico, aq.finalizada)';
        }

        
        $sql = str_replace('`', '', $sql);
        
        return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    public function getAvaliacoesRespondidasByEmpresas($params, $diretor = false){
        return $this->getTableGateway()->select(function($select) use ($params, $diretor) {

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_medico_avaliacao_qualitativa.empresa',
                    array('nome_empresa')
                );

            $select->join(
                    array('m' => 'tb_medicos'),
                    'm.id = medico',
                    array('nome_medico')
                );

            if($diretor){
                $select->where
                    ->nest
                        ->equalTo('m.usuario_diretor', $diretor)
                        ->or
                        ->equalTo('m.usuario_diretor2', $diretor)
                    ->unnest;
            }
            //$select->where(array('m.ativo' => 'S'));
            
            $select->where($params);

        }); 
    }

    //Pesquisar avaliações do auditor
    public function getAvaliacoesRespondidasAuxiliarByEmpresas($params){
        return $this->getTableGateway()->select(function($select) use ($params) {

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_medico_avaliacao_qualitativa.empresa',
                    array('nome_empresa')
                );
            
            $select->join(
                    array('ae' => 'tb_usuario_auxiliar_empresas'),
                    'ae.empresa = e.id',
                    array() 
                );

            $select->join(
                    array('m' => 'tb_medicos'),
                    'm.id = medico',
                    array('nome_medico')
                );

            $select->where($params);
        }); 
    }

    public function getAvaliacoesByPeriodo($ano, $mes){
        return $this->getTableGateway()->select(function($select) use ($ano, $mes) {
            $select->join(
                    array('m' => 'tb_medicos'),
                    'm.id = tb_medico_avaliacao_qualitativa.medico',
                    array('nome_medico')
                );

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_medico_avaliacao_qualitativa.empresa',
                    array('nome_empresa')
                );

            $select->join(
                    array('ap' => 'tb_medico_avaliacao_pid'),
                    'ap.medico = m.id AND ap.empresa = e.id',
                    array('id_pid' => 'id', 'pontos_positivos', 'pontos_desenvolver', 'plano_acao', 'desenvolvimento_semestral',
                          'desenvolvimento_anual', 'feedback_informal', 'meta_medio_prazo', 'arquivo_pid' => 'arquivo'
                        ),
                    'LEFT'
                );

            $select->where(array('tb_medico_avaliacao_qualitativa.ano' => $ano, 
                                 'tb_medico_avaliacao_qualitativa.mes' => $mes,
                            ));
            $select->where->notEqualTo('tb_medico_avaliacao_qualitativa.auditado', 'S');
            
            $select->where->nest 
                        ->equalTo('ap.ano', $ano)
                        ->equalTo('ap.mes', $mes)
                        ->notEqualTo('ap.auditado', 'S')
                        ->or
                        ->isNull('ap.auditado');

            $select->order('e.nome_empresa, m.nome_medico');
        });
    }

    public function getavaliacoesAuditar($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_medico_avaliacao_qualitativa.empresa',
                    array('nome_empresa')
                );

            $select->join(
                    array('m' => 'tb_medicos'),
                    'm.id = tb_medico_avaliacao_qualitativa.medico',
                    array('nome_medico')
                );

            if(isset($params['empresa'])){
                //colocar nome da tabela no parâmetro empresa
                $params['tb_medico_avaliacao_qualitativa.empresa'] = $params['empresa'];
                unset($params['empresa']);
            }
            
            $select->where($params);

            $select->order('ano, mes, nome_empresa, nome_medico');
            
        }); 
    }
}
?>