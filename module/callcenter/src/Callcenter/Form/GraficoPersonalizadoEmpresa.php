<?php

 namespace Callcenter\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class GraficoPersonalizadoEmpresa extends BaseForm {
     
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

        //empresa
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S', 'callcenter' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
        
        $primeirosElementos = array('' => '-- Selecione --', 'T' => 'Todas as empresas');
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'), $primeirosElementos);
        $this->_addDropdown('empresa', 'Empresa:', false, $empresas);
        

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

 }
?>