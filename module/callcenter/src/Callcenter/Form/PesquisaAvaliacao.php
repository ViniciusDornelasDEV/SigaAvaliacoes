<?php

 namespace Callcenter\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class PesquisaAvaliacao extends BaseForm {
     
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

        $this->_addDropdown('respondidas', 'Avaliação respondida:', true, array('N' => 'Não', 'S' => 'Sim'));

        //empresa
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S', 'callcenter' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
    
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'), array('' => 'Todas as empresas'));
        $this->_addDropdown('empresa', 'Empresa:', false, $empresas);

        //data de referencia
        $servicePilhaAvaliacao = $this->serviceLocator->get('PilhaAvaliacaoCallCenter');
        $avaliacoes = $servicePilhaAvaliacao->getPeriodosAvaliacao()->toArray();
        $periodos = array();
        foreach ($avaliacoes as $avaliacao) {
            $data = parent::converterData($avaliacao['data_referencia']);
            $periodos[$avaliacao['data_referencia']] = $data;
        }
        $this->_addDropdown('data_referencia', 'Data de referência:', true, $periodos);        

        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    

 }
