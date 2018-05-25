<?php

 namespace Callcenter\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class Grafico extends BaseForm {
     
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

        $this->_addDropdown('tipoRelatiorio', 'Tipo:', false, array('1' => 'Gráfico', 
                                                                    '2' => 'Todas as empresas', 
                                                                    '3' => 'Individual',
                                                                    '4' => 'Banco de dados'
                                                                ));      

        $this->genericTextInput('inicio', '* Data de início: ', true);

        $this->genericTextInput('fim', '* Data de término: ', true);
        
        //empresa
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecordsFromArray(array('ativo' => 'S', 'callcenter' => 'S'), 'nome_empresa', array('id', 'nome_empresa'));
    
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_empresa'), array('' => 'Todas as empresas'));
        $this->_addDropdown('empresa', 'Empresa:', false, $empresas);
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function getData($flag = 17){
        $data = parent::getData($flag);
        $data['inicio'] = parent::converterData($data['inicio']);
        $data['fim'] = parent::converterData($data['fim']);
        return $data;
    }

 }
