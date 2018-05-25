<?php

 namespace Avaliacaodiaria\Form;
 
 use Application\Form\Base as BaseForm; 

 class Avaliacao extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
  public function __construct($name, $serviceLocator, $campos)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);
       
        parent::__construct($name);   

        foreach ($campos as $campo) {
            if($campo['aparecer'] == 'S'){
                if($campo['obrigatorio'] == 'S'){
                    $required = true;
                    $prefixo = '* ';
                }else{
                    $required = false;
                    $prefixo = '';
                }

                $label = '<i class="tooltip"><b>'.$campo['tooltip'].'</b></i><strong>'.$prefixo.$campo['label'].'</strong>';
                $textInput = array('tme', 'tma', 'atraso');
                if(in_array($campo['nome_campo'], $textInput)){
                    $this->genericTextInput($campo['nome_campo'], $label, $required, '', '', 'max-width:150px;');
                }else{
                    $this->integerNumberInput($campo['nome_campo'], $label, $required);
                }
                
            }
        }
       

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function desabilitarCampos(){
        /*$this->get('meta_agendamento')->setAttribute('disabled', 'disabled');
        $this->get('meta_agendamento_mensal')->setAttribute('disabled', 'disabled');
        $this->get('meta_agendamento_mensal_dias')->setAttribute('disabled', 'disabled');
        $this->get('diferenca')->setAttribute('disabled', 'disabled');
        $this->get('meta_mensal')->setAttribute('disabled', 'disabled');
        $this->get('dias_meta')->setAttribute('disabled', 'disabled');*/
    }
    
    public function removerCampos(){
        /*$this->getInputFilter()->remove('meta_agendamento');
        $this->getInputFilter()->remove('meta_agendamento_mensal');
        $this->getInputFilter()->remove('meta_agendamento_mensal_dias');
        $this->getInputFilter()->remove('diferenca');
        $this->getInputFilter()->remove('dias_meta');
        $this->getInputFilter()->remove('dias_meta');*/
    }

 }