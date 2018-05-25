<?php
namespace Callcenter\Model;

use Avaliacao\Model\Base;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

class PilhaAvaliacao Extends Base {
	public function getAvaliacoesAbertas($date, $empresa){
            $adapter = $this->tableGateway->getAdapter();
            $empresa = $adapter->platform->quoteIdentifier($empresa);
            $date = $adapter->platform->quoteIdentifier($date);
            $sql = 'SELECT c.*, ac.id AS id_callcenter, ac.inicio, ac.termino, ac.data_referencia
                    FROM tb_pilha_avaliacoes_callcenter AS ac
                    LEFT JOIN tb_callcenter AS c ON c.data_referencia = ac.data_referencia AND c.empresa = '.$empresa.'
                    WHERE "'.$date.'" >= inicio AND "'.$date.'" <= termino AND c.id IS NULL
                    ORDER BY ac.data_referencia';
            
            $sql = str_replace('`', '', $sql);

            return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    public function getAvaliacoesRespondidas($date, $usuario){
            $adapter = $this->tableGateway->getAdapter();
            $usuario = $adapter->platform->quoteIdentifier($usuario);
            $date = $adapter->platform->quoteIdentifier($date);
            $sql = 'SELECT c.*, ac.id AS id_callcenter, ac.inicio, ac.termino, ac.data_referencia
                    FROM tb_pilha_avaliacoes_callcenter AS ac
                    INNER JOIN tb_callcenter AS c ON c.data_referencia = ac.data_referencia AND c.usuario = '.$usuario.'
                    WHERE "'.$date.'" >= inicio AND "'.$date.'" <= termino 
                    ORDER BY ac.data_referencia';
            
            $sql = str_replace('`', '', $sql);
            return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
	public function getPeriodosAvaliacao(){
		    $adapter = $this->tableGateway->getAdapter();
            $sql = 'SELECT ac.*
                    FROM tb_pilha_avaliacoes_callcenter AS ac
                    ORDER BY ac.data_referencia DESC';
            
            $sql = str_replace('`', '', $sql);
            return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
	}

    public function inserir($dados, $pluginDias){
        $inicio = new \DateTime($dados['inicio']);
        $fim = new \DateTime($dados['termino']);
        $fim->modify('+1 day');

        $interval = new \DateInterval('P1D');
        $periodo = new \DatePeriod($inicio, $interval ,$fim);
        $connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            foreach($periodo as $data){
                $dataAvaliacao = $data->format("Y-m-d");
                //se data de avaliação for um final de semana, inserir o proximo dia útil como limite
                if($pluginDias->diaNaoUtil($dataAvaliacao)){
                    $proximosDias = $pluginDias->feriadosProximos($dataAvaliacao, true);
                    $dataTermino = $proximosDias[count($proximosDias)-1];
                }else{
                    $dataTermino = $dataAvaliacao;
                }

                $dados = array(
                            'inicio'            =>  $dataAvaliacao, 
                            'termino'           =>  $dataTermino,
                            'data_referencia'   =>  $dataAvaliacao,
                        );
                //inserir data de avaliação
                $this->insert($dados);
            }

            $connection->commit();
            return true;
            
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
    }

    public function alterarDatas($dados){
            $adapter = $this->tableGateway->getAdapter();
            $ref_inicio = $adapter->platform->quoteIdentifier($dados['referencia_inicio']);
            $ref_termino = $adapter->platform->quoteIdentifier($dados['referencia_termino']);
            $termino = $adapter->platform->quoteIdentifier($dados['termino']);
            
            $sql = 'UPDATE tb_pilha_avaliacoes_callcenter SET termino = "'.$termino.'" WHERE data_referencia BETWEEN "'.$ref_inicio.'" AND "'.$ref_termino.'";';
            
            $sql = str_replace('`', '', $sql);
            return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

}
?>