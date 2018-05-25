<?php

 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 

 class BaseAvaliacao extends BaseForm {
     
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
        
        //Pesquisar status para todos os campos
        $serviceStatus = $this->serviceLocator->get('Status');
        $status = $serviceStatus->getRecordsFromArray(array('ativo' => 'S'));
        $status = $serviceStatus->prepareForDropDown($status, array('id', 'nome'));

        foreach ($campos as $campo) {
            if($campo['aparecer'] == 'S'){
                $prefixo = '';
                $obrigatorio = false;
                if($campo['obrigatorio'] == 'S'){
                    $prefixo = ' * ';
                    $obrigatorio = true;
                }
                $label = '<i class="tooltip"><b>'.$campo['tooltip'].'</b></i><strong>'.$prefixo.$campo['label'].'</strong>';
                $this->gerarPergunta($campo['nome_campo'], $label, $campo['nome_campo'], $status, $obrigatorio);
            }
        }

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
