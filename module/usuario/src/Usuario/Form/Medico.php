<?php

 namespace Usuario\Form;
 
use Application\Form\Base as BaseForm;
 
 class Medico extends BaseForm
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

        $this->genericTextInput('nome_medico', '* Nome do médico:', true, 'Nome do médico');

        //Vincular médico
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
    
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'));
        $this->_addDropdown('empresa', '* Empresa:', true, $empresas);
        
        //DADOS DO USUÁRIO
        $this->genericTextInput('login', 'Login:', false, 'Login do médico');
       
        $this->_addPassword('senha', 'Senha: ', 'Senha', false, false);
        
        $this->_addPassword('confirma_senha', 'Confirma senha: ', 'Confirmar senha', false, false);


        //diretor 2
        $serviceUsuario = $this->serviceLocator->get('Usuario');
        $usuarios = $serviceUsuario->getUsuariosByParams(array('id_usuario_tipo' => 8, 'ativo' => 'S'));
        $usuarios = $serviceUsuario->prepareForDropDown($usuarios, array('id', 'nome'));
        $this->_addDropdown('usuario_diretor2', 'Diretor 2:', false, $usuarios);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
