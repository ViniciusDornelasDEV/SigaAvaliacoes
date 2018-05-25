<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class Login extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name)
    {
        parent::__construct($name);
        $this->genericTextInput('login', 'Usuário', true, 'Usuário');
        $this->_addPassword('password', 'Password', 'Senha');
        //$this->_addCheckbox('remember_me', 'Remember me', false, '');

        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));
    }
 }
