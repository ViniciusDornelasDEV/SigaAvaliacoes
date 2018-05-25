<?php

 namespace Audicao\Form;
 
 use Application\Form\Base as BaseForm; 

 class PesquisaAvaliacaoAudicao extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {
        parent::__construct($name);        

        $this->setServiceLocator($serviceLocator);  


        $serviceAbas = $this->serviceLocator->get('Abas');
        $abas = $serviceAbas->fetchAll(array('id', 'nome'))->toArray();
        //não auditar callcentr por enquanto
        unset($abas[7]);
        unset($abas[9]);
        
        $abas = $serviceAbas->prepareForDropDown($abas, array('id', 'nome'), array());
        $this->_addDropdown('aba', '* Formulário: ', true, $abas);

        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array(), 'nome_empresa');
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'));
        $this->_addDropdown('empresa', 'Empresa: ', false, $empresas);
        
        $meses = array('' => '-- Selecione --', '1' => 'Janeiro', '2' => 'Fevereiro', '3' => 'Março', '4' => 'Abril', '5' => 'Maio', '6' => 'Junho', 
                        '7' => 'Julho', '8' => 'Agosto', '9' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro');
        
        $this->_addDropdown('mes', 'Mês:', false, $meses);

        $this->_addDropdown('respondida', 'Status: ', true, array('S' => 'Avaliações respondidas', 'N' => 'Avaliações não respondidas'));

        $anos = array('' => '-- Selecione --');
        for ($i = 2015; $i <= date('Y'); $i++) { 
            $anos[$i] = $i;
        }
        
        $this->_addDropdown('ano', 'Ano:', false, $anos);
                
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
