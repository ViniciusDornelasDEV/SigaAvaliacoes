<?php

namespace Application\Model;

use Zend\Db\Metadata\Metadata;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;

/**
 * This is base table object inherited by all child table objects.
 * Say hello and make your introductions. 
 *
 * @author  Wagner Silva <wagner@techstudiohq.com>
 * @version 1.0
 */
class BaseTable {

    /**
     * Table Gateway.
     * 
     * @var mixed
     * @access private
     */
    public $tableGateway;

    /**
     * Service Locator.
     * 
     * @var mixed
     * @access private
     */
    private $serviceLocator;


    /**
     * __construct function.
     * 
     * @access public
     * @param TableGateway $tableGateway
     * @return void
     */
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }


    /**
     * setServiceLocator function.
     * 
     * @access public
     * @param ServiceManager $serviceLocator
     * @return void
     */
    public function setServiceLocator(ServiceManager $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * getServiceLocator function.
     * 
     * @access public
     * @return void
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
    
    public function getIdentity($property = null) {
        $storage = $this->getServiceLocator()->get('session');

        if (!$storage) {
            return false;
        }
        
        $data = $storage->read();
                        
        if ($property && isset($data[$property])) {
            return $data[$property];
        }
        
        return $data;
    }

    /**
     * getTableGateway function.
     * 
     * @access public
     * @return void
     */
    public function getTableGateway() {
        return $this->tableGateway;
    }

    /**
     * Fetch all records in this table.
     * 
     * @access public
     * @return void
     */
    public function fetchAll($fields = array('*')) {
        return $this->getTableGateway()->select(function($select) use ($fields) {
            $select->columns($fields);
        });

        /*$resultSet = $this->getTableGateway()->select();
        $resultSet->columns(array('id', 'uf'));*/
        return $resultSet;
    }

    public function prepareForDropDown($data, array $campos, $preparedArray = array('' => '-- selecione --'), $separator = false){
        if($data){
            foreach ($data as $record) {
                $descricao = $record[$campos[1]];
                if(isset($campos[2])){
                    if($separator){
                        $descricao .= $separator.$record[$campos[2]];
                    }else{
                        $descricao .= $record[$campos[2]];
                    }
                }
                $preparedArray[$record[$campos[0]]] = $descricao;
            }
        }

        return $preparedArray;
    }

    public function getNextInsertId($table) {
        $sql = "SHOW TABLE STATUS FROM timesistemas1 LIKE '".$table."'" ;
        $rowset = $this->getTableGateway()->getAdapter()->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        if(!$row = $rowset->current()){
            return false;
        }

        return $row;
    }

    /**
     * Get an individual record based on an ID.
     * You also have the option for searching a particular column.
     * 
     * @access public
     * @param mixed $value
     * @param string $column (default: 'id')
     * @return void
     */
    public function getRecord($value, $column = 'id') {
        $rowset = $this->getTableGateway()->select(array($column => $value));
        if (!$row = $rowset->current()) {
            return false;
        }

        return $row;
    }

    /**
     * Gets a record set based on an ID.
     * You also have the option for searching a particular column.
     * 
     * @access public
     * @param mixed $value
     * @param string $column (default: 'id')
     * @return void
     */
    public function getRecords($value, $column = 'id', $fields = array('*'), $orderByString = false) {

        $rowset = $this->getTableGateway()->select(function($select) use ($fields, $value, $column, $orderByString) {
            $select->columns($fields);
            $select->where(array($column => $value));
            if ($orderByString) {
                    $select->order($orderByString);
            }
        });
        
        return $rowset;
    }

    /**
     * Format the obfuscated value into a valid
     * ORDER BY value.
     * 
     * @access public
     * @param mixed $orderByString (default: null)
     * @return void
     */
    public function formatOrderBy($orderByString = null) {
        if (!$orderByString) {
            return false;
        }
        $array = explode('__', $orderByString);
        return $array[0] . ' ' . strtoupper($array[1]);
    }

    /**
     * Get a result set based on criteria array.
     * 
     * @access 	public
     * @param 	array $where (default: array())
     * @return 	void
     */
    public function getRecordsFromArray(array $where = array(), $orderByString = false, $fields = array('*')) {
        $self = $this;
        $records = $this->getTableGateway()->select(function(Select $select) use ($where, $orderByString, $self, $fields) {
            $select->columns($fields);
            if (!empty($where))
                $select->where($where);

            if ($orderByString) {
                $select->order($orderByString);
            }
        });

        return $records;
    }
    

    /**
     * Get result set based on like operators.
     * 
     * @access public
     * @param array $criteria
     * @return void
     */
    public function getRecordsFromLike(array $criteria) {
        return $this->getTableGateway()->select(function(Select $select) use ($criteria) {

                    $predicates = array();

                    foreach ($criteria as $column => $value) {
                        $predicate = new Predicate\Predicate;
                        $predicates[] = $predicate->like($column, $value);
                    }

                    if (count($predicates)) {
                        $select->where(array(
                            new Predicate\PredicateSet($predicates)
                        ));
                    }
                });
    }

    /**
     * Get an individual record based on where array.
     * 
     * @access public
     * @param array $where (default: array())
     * @return void
     */
    public function getRecordFromArray(array $where = array()) {
        $rowset = $this->getTableGateway()->select($where);

        if (!$row = $rowset->current()) {
            return false;
        } 

        return $row;
    }

    /**
     * Save a record.
     * 
     * @access public
     * @return int
     */
    public function save($record) {
        $id = isset($record['id']) ? (int) $record['id'] : false;
        $data = $this->sanitizeObjectForEntry($record);

        if (count($data)) {
            if (!$id) {
                $this->getTableGateway()->insert($data);
                return $this->getTableGateway()->getLastInsertValue();
            } else {

                $res = $this->getTableGateway()->update($data, array('id' => $id));
                
                return $res;
            }
        }
    }
    
    
    /**
     * Updates a record.
     * 
     * @access public
     * @return int
     */
    public function update($data, array $where) {
        $data = $this->sanitizeObjectForEntry($data);
        $this->logSistema($data, 'update');       
        $res = $this->getTableGateway()->update($data, $where);               
        return $res;
        
    }
    

    /**
     * Save a record for a logged in user.
     * 
     * @access public
     * @return int
     */
    public function saveForCurrentUser($record) {          

        $user = $this->getIdentity();
                
        if(!$user)
            throw new \Exception('User not logged in');

        $record['user'] = $user['id'];
                        
        return $this->save($record);

    }
    
    /**
     * Get a record for current user
     * 
     * @access public
     * @return int
     */
    public function getRecordForCurrentUser($record_id) {          

        $user = $this->getIdentity();
        
        if(!$user)
            throw new \Exception('User not logged in');
        
        $res = $this->getRecordFromArray(array(
            'user'=>$user['id'],
            'id'=>$record_id,
        ));
        
        return $res;

    }
    
    /**
     * Get a result set based on criteria array, for the current user.
     * 
     * @access 	public
     * @param 	array $where (default: array())
     * @return 	void
     */
    
    public function getRecordsForCurrentUser(array $params = array (), $orderByString = null) {          

        $user = $this->getIdentity();
        
        if(!$user)
            throw new \Exception('User not logged in');        
        
        $params['user'] = $user['id'];
        
        $res = $this->getRecordsFromArray($params, $orderByString);
        
        return $res;

    }

    /**
     * Save a record.
     * 
     * @access public
     * @return int
     */
    public function insert($record) {

        $data = $this->sanitizeObjectForEntry($record);

        if (count($data)) {
            //Criar log do sistema
            $this->logSistema($data, 'insert');
            $this->getTableGateway()->insert($data);
            return $this->getTableGateway()->getLastInsertValue();
        }
    }

    public function logSistema($data, $operacao){
        //verificar se existe pasta para a data corrente
        $caminho = 'public/log/'.date('d-m-Y');
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho .= '/'.$this->tableGateway->getTable();
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        //verifica se existe arquivo para a operacao
        $nomeArquivo = $caminho.'/'.$operacao.'.txt';
        $arquivo = fopen($nomeArquivo, 'a');
        //tratart vetor para texto
        $string = $this->vetorToString($data);

        fwrite($arquivo, $string);
        fclose($arquivo);
    }

    public function vetorToString($data){
        $usuario = $this->getIdentity();
        $string = 'usuario: '.$usuario['id'].' - '.$usuario['login']."\r\n";
        $string .= 'Data/Hora da transação: '.date('d-m-Y H:i:s')."\r\n";
        $string .= 'Informações: ';

        foreach ($data as $indice => $informacao) {
            if(!empty($informacao)){
                $string .= $indice.' => '.$informacao."\r\n";
            }
        }

        $string .= '------------------------------------------------------'."\r\n"."\r\n";
        return $string."";
    }

    /**
     * Delete a record. The $whereArray is VERY important here!
     * 
     * @access public
     * @param mixed $id
     * @return void
     */
    public function delete(array $whereArray = array()) {
        $this->logSistema($whereArray, 'delete');
        return $this->getTableGateway()->delete($whereArray);
    }

    public function sanitizeObjectForEntry($object) {
        $metadata = new Metadata($this->getTableGateway()->getAdapter());
        $tableColumns = array_flip(@$metadata->getColumnNames($this->getTableGateway()->getTable()));
        foreach ($object as $key => $val) {
            if (!array_key_exists($key, $tableColumns)) {
                unset($object[$key]);
            } else {

                if ($object[$key] == '') {
                    $object[$key] = null;
                } else {
                    $object[$key] = strip_tags($object[$key]);
                }
            }
        }

        return $object;
    }

}
