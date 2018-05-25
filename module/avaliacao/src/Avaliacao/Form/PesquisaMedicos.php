<?php

 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class PesquisaMedicos extends BaseForm {
     
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
        
        $this->_addDropdown('respondida', 'Avaliação respondida:', false, array(
            ''  => 'Todas',
            'N' => 'Não respondido', 
            'Q' => 'Qualitativa respondida', 
            'R' => 'Respondida'
        ));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    

 }
