<?php

 namespace Callcenter\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class GraficoPersonalizado extends BaseForm {
     
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

        $this->genericTextInput('inicio', '* Data de início: ', true);

        $this->genericTextInput('fim', '* Data de término: ', true);
        

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function setData($data){
        $data['inicio'] = parent::converterData($data['inicio']);
        $data['fim'] = parent::converterData($data['fim']);
        return parent::setData($data);
    }

 }
