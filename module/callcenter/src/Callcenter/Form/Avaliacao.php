<?php

 namespace Callcenter\Form;
 
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
                if($campo['categoria_questao'] == 32){
                    $this->genericTextInput($campo['nome_campo'], $label, $required, '');
                }else{
                    $textInput = array('confirmacao_geral', 'agendamento_geral_porcentagem',
                        'abandonadas_atendidas_hora', 'abandonadas_atendidas_dia', 'abandono_callback', 'confirmacao_geral',
                        'd0', 'd1', 'd2', 'tempo_espera', 'tempo_atendimento', 'tempo_logado', 'tempo_espera', 'tempo_atendimento', 
                        'tempo_logado');

                    if(in_array($campo['nome_campo'], $textInput)){
                        $this->genericTextInput($campo['nome_campo'], $label, $required, '', '', 'max-width:150px;');
                    }else{
                        if($campo['nome_campo'] == 'meta_mensal' || $campo['nome_campo'] == 'meta_agendamento_mensal_dias'){
                            //campos verdes
                            $this->genericTextInput($campo['nome_campo'], $label, false, '', '', 'max-width:150px;background: #d0e9c6 !important;');
                        }else{
                            if($campo['nome_campo'] == 'meta_agendamento_mensal' || $campo['nome_campo'] == 'dias_meta'){
                                //campos vermelhos
                                $this->genericTextInput($campo['nome_campo'], $label, $required, '', '', 'max-width:150px;background: #ebcccc !important;');
                            }else{
                                $this->integerNumberInput($campo['nome_campo'], $label, $required);
                            }   
                        }
                    }
                }
            }
        }
       

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function desabilitarCampos(){
        $this->get('meta_agendamento')->setAttribute('disabled', 'disabled');
        $this->get('meta_agendamento_mensal')->setAttribute('disabled', 'disabled');
        $this->get('meta_agendamento_mensal_dias')->setAttribute('disabled', 'disabled');
        $this->get('diferenca')->setAttribute('disabled', 'disabled');
        $this->get('meta_mensal')->setAttribute('disabled', 'disabled');
        $this->get('dias_meta')->setAttribute('disabled', 'disabled');
    }
    
    public function removerCampos(){
        $this->getInputFilter()->remove('meta_agendamento');
        $this->getInputFilter()->remove('meta_agendamento_mensal');
        $this->getInputFilter()->remove('meta_agendamento_mensal_dias');
        $this->getInputFilter()->remove('diferenca');
        $this->getInputFilter()->remove('dias_meta');
        $this->getInputFilter()->remove('dias_meta');
    }

 }