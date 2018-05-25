<?php

 namespace Callcenter\Form;
 
use Application\Form\Base as BaseForm;
 
 class Meta extends BaseForm
 {
     
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
        
        //empresa
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S', 'callcenter' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
    
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'));
        $this->_addDropdown('empresa', '* Empresa:', true, $empresas);

		$meses = array('' => '-- Selecione --', '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril', '05' => 'Maio', '06' => 'Junho', 
                        '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro');
        
        $this->_addDropdown('mes', '* Mês:', true, $meses);

        
        $anos = array('' => '-- Selecione --');
        for ($i = 2015; $i <= date('Y')+1; $i++) { 
            $anos[$i] = $i;
        }
        
        $this->_addDropdown('ano', '* Ano:', true, $anos);

        
        $this->integerNumberInput('valor', '* Meta:', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }