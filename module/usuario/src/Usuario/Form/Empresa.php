<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class Empresa extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {

        $this->setServiceLocator($serviceLocator);

        parent::__construct($name);        
        $this->genericTextInput('nome_empresa', '* Empresa:', true, 'Nome da empresa');

        $this->genericTextInput('nome_responsavel', '* Responsável:', true, 'Funcionário responsável');
        
        $this->addEmailElement('email', '* Email:', true, 'Email');

        $this->addEmailElement('email2', 'Email:', false, 'Email');

        $this->addEmailElement('email3', 'Email:', false, 'Email');
        
        $this->_addDropdown('callcenter', 'Callcenter:', false, array('N' => 'Não', 'S' => 'Sim'));

        $this->_addDropdown('avaliacao_diaria', 'Avaliação diária:', false, array('N' => 'Não', 'S' => 'Sim'));

        $serviceEstado = $this->serviceLocator->get('Estado');
        $estados = $serviceEstado->fetchAll(array('id', 'uf'))->toArray();
        
        $estados = $serviceEstado->prepareForDropDown($estados, array('id', 'uf'));

        $this->_addDropdown('estado', 'Estado:', false, $estados, 'CarregaCidade(this.value);');
        
        //cd_cidade
        $this->_addDropdown('cidade', 'Cidade:', false, array('' => '-- Selecione --'));

        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
        //$this->addSubmit('Salvar', 'btn btn-success');
    }

    public function setCidadesByEstado($estado){
        //buscar cidade
        $serviceCidade = $this->serviceLocator->get('Cidade');
        $cidades = $serviceCidade->getRecords($estado, 'estado');
        $cidades = $serviceCidade->prepareForDropDown($cidades, array('id', 'nome'));
        //Setando valores
        $cidades = $this->get('cidade')->setAttribute('options', $cidades);
        
        return $cidades;      
    }

    public function setData($data){
        if(isset($data['estado'])){
            $this->setCidadesByEstado($data['estado']);
        }

        if(isset($data['cidade']) && !isset($data['estado'])){
            //find estado by cidade
            $cidade = $this->serviceLocator->get('Cidade')->getRecord($data['cidade']);
            $data['estado'] = $cidade->estado;
            $this->setCidadesByEstado($data['estado']);
        }
        
        parent::setData($data);
    }
 }
