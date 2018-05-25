<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
 
 class MudaEmpresa extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $idUsuario)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);
        //EMPRESA
        $serviceAbasUsuario = $this->serviceLocator->get('AbasUsuario');
        $empresas = $serviceAbasUsuario->getEmpresasUsuario($idUsuario);
        
        $empresas = $serviceAbasUsuario->prepareForDropDown($empresas, array('empresa', 'nome_empresa'));

        $this->_addDropdown('empresa', '* Empresa:', true, $empresas);
        
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
