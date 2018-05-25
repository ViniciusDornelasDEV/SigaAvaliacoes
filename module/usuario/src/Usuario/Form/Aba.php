<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
 
 class Aba extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, Array $abasVinculadas)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);
        //EMPRESA
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
        
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'));

        $this->_addDropdown('empresa', '* Empresa:', true, $empresas);

        //Abas
        $serviceAbas = $this->serviceLocator->get('Abas');
        $abas = $serviceAbas->fetchAll(array('id', 'nome'));

        $abas = $serviceAbas->prepareForDropDown($abas, array('id', 'nome'));
        $this->_addDropdown('abas', '* Aba:', true, $abas);
        
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
