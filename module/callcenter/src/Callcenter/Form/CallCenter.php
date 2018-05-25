<?php

 namespace Callcenter\Form;
 
use Application\Form\Base as BaseForm;
 
 class CallCenter extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {

        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);

        $this->genericTextInput('nome_operador', '* Nome do operador:', true, 'Nome do operador');

        //Vincular empresa
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S', 'callcenter' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
    
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'));
        $this->_addDropdown('empresa', '* Empresa:', true, $empresas);
        
        //DADOS DO USUÁRIO
        $this->genericTextInput('login', '* Login:', true, 'Login do médico');

        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
