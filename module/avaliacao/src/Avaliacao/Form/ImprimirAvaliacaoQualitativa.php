<?php
 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 

 class ImprimirAvaliacaoQualitativa extends BaseForm {
     
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

        $serviceOpcoes = $this->serviceLocator->get('CampoMedicoOpcoes');

        $campos = parent::prepararCamposDinamicos($campos);
        if($campos['qualidade']['aparecer'] == 'S'){
            $opcoes = $serviceOpcoes->getRecords($campos['qualidade']['campo'], 'campo')->toArray();
            $opcoes = $serviceOpcoes->prepareForDropDown($opcoes, array('id', 'opcao'), array());

            $obrigatorio = $this->booleanByAtivo($campos['qualidade']['obrigatorio']);

            $this->_addRadio('qualidade', $obrigatorio['prefixo'].$campos['qualidade']['label'], $obrigatorio['obrigatorio'], $opcoes);
            
        }

        if($campos['relacionamento_comunidade']['aparecer'] == 'S'){
            $opcoes = $serviceOpcoes->getRecords($campos['relacionamento_comunidade']['campo'], 'campo')->toArray();
            $opcoes = $serviceOpcoes->prepareForDropDown($opcoes, array('id', 'opcao'), array());

            $obrigatorio = $this->booleanByAtivo($campos['relacionamento_comunidade']['obrigatorio']);
            $this->_addRadio('relacionamento_comunidade', $obrigatorio['prefixo'].$campos['relacionamento_comunidade']['label'], $obrigatorio['obrigatorio'], $opcoes);
            
        }

        if($campos['difusao_conhecimento']['aparecer'] == 'S'){
            $opcoes = $serviceOpcoes->getRecords($campos['difusao_conhecimento']['campo'], 'campo')->toArray();
            $opcoes = $serviceOpcoes->prepareForDropDown($opcoes, array('id', 'opcao'), array());

            $obrigatorio = $this->booleanByAtivo($campos['difusao_conhecimento']['obrigatorio']);
            $this->_addRadio('difusao_conhecimento', $obrigatorio['prefixo'].$campos['difusao_conhecimento']['label'], $obrigatorio['obrigatorio'], $opcoes);
            
        }

        if($campos['relacionamento_interno']['aparecer'] == 'S'){
            $opcoes = $serviceOpcoes->getRecords($campos['relacionamento_interno']['campo'], 'campo')->toArray();
            $opcoes = $serviceOpcoes->prepareForDropDown($opcoes, array('id', 'opcao'), array());

            $obrigatorio = $this->booleanByAtivo($campos['relacionamento_interno']['obrigatorio']);
            $this->_addRadio('relacionamento_interno', $obrigatorio['prefixo'].$campos['relacionamento_interno']['label'], $obrigatorio['obrigatorio'], $opcoes);
            
        }

        if($campos['producao_cientifica']['aparecer'] == 'S'){
            $opcoes = $serviceOpcoes->getRecords($campos['producao_cientifica']['campo'], 'campo')->toArray();
            $opcoes = $serviceOpcoes->prepareForDropDown($opcoes, array('id', 'opcao'), array());
            
            $obrigatorio = $this->booleanByAtivo($campos['producao_cientifica']['obrigatorio']);
            $this->_addRadio('producao_cientifica', $obrigatorio['prefixo'].$campos['producao_cientifica']['label'], $obrigatorio['obrigatorio'], $opcoes);
            
        }
               
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
