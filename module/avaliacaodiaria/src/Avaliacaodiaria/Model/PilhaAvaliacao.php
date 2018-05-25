<?php
namespace Avaliacaodiaria\Model;

use Avaliacao\Model\Base;
use Zend\Db\Sql\select;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

class PilhaAvaliacao Extends Base {
	public function getAvaliacoesAbertas($date, $empresa){
            $adapter = $this->tableGateway->getAdapter();
            $empresa = $adapter->platform->quoteIdentifier($empresa);
            $date = $adapter->platform->quoteIdentifier($date);
            $sql = 'SELECT c.*, ac.id AS id_avaliacaodiaria, ac.inicio, ac.termino, ac.data_referencia
                    FROM tb_pilha_avaliacoes_avaliacaodiaria AS ac
                    LEFT JOIN tb_avaliacaodiaria AS c ON c.data_referencia = ac.data_referencia AND c.empresa = '.$empresa.'
                    WHERE "'.$date.'" >= inicio AND "'.$date.'" <= termino AND c.id IS NULL
                    ORDER BY ac.data_referencia';
            
            $sql = str_replace('`', '', $sql);

            return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    public function getAvaliacoesRespondidas($date, $usuario){
            $adapter = $this->tableGateway->getAdapter();
            $usuario = $adapter->platform->quoteIdentifier($usuario);
            $date = $adapter->platform->quoteIdentifier($date);
            $sql = 'SELECT c.*, ac.id AS id_avaliacaodiaria, ac.inicio, ac.termino, ac.data_referencia
                    FROM tb_pilha_avaliacoes_avaliacaodiaria AS ac
                    INNER JOIN tb_avaliacaodiaria AS c ON c.data_referencia = ac.data_referencia AND c.usuario = '.$usuario.'
                    WHERE "'.$date.'" >= inicio AND "'.$date.'" <= termino 
                    ORDER BY ac.data_referencia';
            
            $sql = str_replace('`', '', $sql);
            return $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
	public function getPeriodosAvaliacao(){
		    $adapter = $this->tableGateway->getAdapter();
            $sql = 'SELECT ac.*
                    FROM tb_pilha_avaliacoes_avaliacaodiaria AS ac
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

}
?>