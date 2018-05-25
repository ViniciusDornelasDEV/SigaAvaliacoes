<?php

 namespace Callcenter\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class GraficoPersonalizadoCampo extends BaseForm {
     
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
        
        //campo de pesquisa
        $serviceCampos = $this->serviceLocator->get('Campo');
        $campos = $serviceCampos->getCamposByAba(8);
    
        $campos = $serviceCampos->prepareForDropDown($campos, array('id', 'label'));
        $this->_addDropdown('campo', 'Campo:', false, $campos);
        

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

 }
?>