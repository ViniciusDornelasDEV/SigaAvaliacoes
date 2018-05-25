<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
 
 class VincularEmpresas extends BaseForm
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
        //EMPRESA
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
        
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'));

        unset($empresas['']);
        $empresas = array('' => '-- Selecione --', 'todas' => 'Todas as empresas') + $empresas;

        $this->_addDropdown('auxiliar_empresa', '* Empresa:', true, $empresas);
        
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
