<?php
 namespace Callcenter\Form;
 
 use Application\Form\Base as BaseForm; 

 class AlterarLiberacao extends BaseForm {
     
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
        
        $this->genericTextInput('referencia_inicio', '* Data de referência, de: ', true);

        $this->genericTextInput('referencia_termino', '* a: ', true);

        $this->genericTextInput('termino', '* Termina em: ', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function setData($data){
        $data['referencia_inicio'] = parent::converterData($data['referencia_inicio']);
        
        $data['referencia_termino'] = parent::converterData($data['referencia_termino']);
        
         $data['termino'] = parent::converterData($data['termino']);

        parent::setData($data);
    }
 }
