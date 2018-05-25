<?php

 namespace Avaliacao\Form;
 
 use Avaliacao\Form\BaseAvaliacao as formAvaliacao;

 class Qualidade extends formAvaliacao {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
    public function __construct($name, $serviceLocator, $campos)
    {
        parent::__construct($name, $serviceLocator, $campos);
    }
 }
