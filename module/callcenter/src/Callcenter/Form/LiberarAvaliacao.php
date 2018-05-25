<?php
 namespace Callcenter\Form;
 
 use Application\Form\Base as BaseForm; 

 class LiberarAvaliacao extends BaseForm {
     
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
        
        //inicio
        $this->genericTextInput('inicio', '* Data de início: ', true);

        //termino
        $this->genericTextInput('termino', '* Data de término: ', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function setData($data){
        $data['inicio'] = parent::converterData($data['inicio']);
        
        if(isset($data['termino'])){
            $data['termino'] = parent::converterData($data['termino']);
        }
        
        parent::setData($data);
    }
 }
