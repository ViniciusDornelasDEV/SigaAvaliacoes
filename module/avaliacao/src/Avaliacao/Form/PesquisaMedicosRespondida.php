<?php

 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class PesquisaMedicosRespondida extends BaseForm {
     
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

        $meses = array('' => '-- Selecione --', '1' => 'Janeiro', '2' => 'Fevereiro', '3' => 'Março', '4' => 'Abril', '5' => 'Maio', '6' => 'Junho', 
                        '7' => 'Julho', '8' => 'Agosto', '9' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro');
        $this->_addDropdown('mes', 'Mês:', false, $meses);

        $anos = array('' => '-- Selecione --');
        for ($i = 2016; $i <= date('Y'); $i++) { 
            $anos[$i] = $i;
        }
        
        $this->_addDropdown('ano', 'Ano:', false, $anos);
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    

 }
