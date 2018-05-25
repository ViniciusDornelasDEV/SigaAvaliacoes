<?php
 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 

 class Ata extends BaseForm {
     
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
        
        $this->genericTextInput('observacoes', '', true, 'Observações');

        $this->addImageFileInput('anexo', '', false);

               
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
