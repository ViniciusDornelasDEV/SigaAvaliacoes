<?php
 namespace Avaliacao\Form;
 
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

        //mes_referencia
        $meses = array('' => '-- Selecione --', '1' => 'Janeiro', '2' => 'Fevereiro', '3' => 'Março', '4' => 'Abril', '5' => 'Maio', '6' => 'Junho', 
                        '7' => 'Julho', '8' => 'Agosto', '9' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro');
        
        $this->_addDropdown('mes_referencia', '* Mês de referência:', true, $meses);

        $ano = date('Y');
        $anoInicio = 2015;
        $anos = array();
        for ($anoInicio; $anoInicio <= $ano ; $anoInicio++) { 
            $anos[$anoInicio] = $anoInicio;

        }

        $this->_addDropdown('ano_referencia', '* Ano de referência:', true, $anos);

        //aba
        $this->_addDropdown('aba', '* Tipo de avaliação (aba):', true, array('C' => 'Clínicas', 'M' => 'Médicos'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function setData($data){
        $data->inicio = parent::converterData($data->inicio);
        $data->termino = parent::converterData($data->termino);
        
        parent::setData($data);
    }
 }
