<?php
 namespace Avaliacaodiaria\Form;
 
 use Application\Form\Base as BaseForm; 

 class ReplicarMetaMensal extends BaseForm {
     
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
        
        $anos = array('' => '-- Selecione --');
        for ($i = 2016; $i <= date('Y')+1; $i++) { 
            $anos[$i] = $i;
        }
        
        $this->_addDropdown('ano_origem', '* Ano de origem:', true, $anos);
        
        $meses = array('' => '-- Selecione --', '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril', '05' => 'Maio', '06' => 'Junho', 
                        '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro');
        
        $this->_addDropdown('mes_origem', '* Mês de origem:', true, $meses);

        $this->_addDropdown('ano_destino', '* Ano de destino:', true, $anos);

        $this->_addDropdown('mes_destino', '* Mês de destino:', true, $meses);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    /*public function setData($data){
        $data['inicio'] = parent::converterData($data['inicio']);
        
        if(isset($data['termino'])){
            $data['termino'] = parent::converterData($data['termino']);
        }
        
        parent::setData($data);
    }*/
 }
