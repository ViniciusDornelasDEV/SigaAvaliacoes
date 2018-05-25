<?php

 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class UploadArquivo extends BaseForm {
     
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
        
        //UPLOAD
        $this->addFileInput('arquivo', '* Arquivo: ', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
        //$this->addSubmit('Salvar', 'btn btn-success');
    }
 }
