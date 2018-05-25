<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class EmpresaArquivos extends BaseForm {
     
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

        $this->genericTextInput('nome', '* Nome:', true, 'Nome do arquivo');   
        
        $this->addFileInput('arquivo', 'Arquivo*: ', true);
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
        //$this->addSubmit('Salvar', 'btn btn-success');
    }
 }
