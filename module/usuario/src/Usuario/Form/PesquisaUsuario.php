<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
 
 class PesquisaUsuario extends BaseForm
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
        $this->genericTextInput('nome', 'Nome do usuário:', false, 'Nome do usuário');

        //TIPO DE USUÁRIO
        $serviceTipoUsuario = $this->serviceLocator->get('UsuarioTipo');
        $tipos = $serviceTipoUsuario->getRecordsFromArray(array(), 'perfil');
        
        $tipos = $serviceTipoUsuario->prepareForDropDown($tipos, array('id', 'perfil'));

        $this->_addDropdown('id_usuario_tipo', ' Tipo de usuário:', false, $tipos);
        
        //EMPRESA
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array(), 'nome_empresa');
        
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'));

        $this->_addDropdown('empresa', ' Empresa:', false, $empresas);
        
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
