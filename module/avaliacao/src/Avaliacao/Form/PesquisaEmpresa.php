<?php

 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class PesquisaEmpresa extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name = null)
    {

        parent::__construct($name);        
        $this->genericTextInput('nome_empresa', 'Empresa:', false, 'Nome da empresa');

        $this->genericTextInput('nome_responsavel', 'Responsável:', false, 'Funcionário responsável');
        
        $this->addEmailElement('email', 'Email:', false, 'Email');

        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
        //$this->addSubmit('Salvar', 'btn btn-success');
    }
 }
