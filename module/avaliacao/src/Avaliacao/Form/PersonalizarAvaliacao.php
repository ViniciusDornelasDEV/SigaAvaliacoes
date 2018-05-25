<?php
 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 

 class PersonalizarAvaliacao extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $campos, $idAba)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);
        $serviceCampoCategoria = $this->serviceLocator->get('CampoCategoria');
        $categoriaQuestao = $serviceCampoCategoria->getRecords($idAba, 'aba')->toArray();
        $categoriaQuestao = $serviceCampoCategoria->prepareForDropDown($categoriaQuestao, array('id', 'nome'));

        foreach ($campos as $campo) {
            $data[$campo['nome_campo'].'label'] = $campo['label'];
            $data[$campo['nome_campo'].'aparecer'] = $campo['aparecer'];
            $data[$campo['nome_campo'].'obrigatorio'] = $campo['obrigatorio'];
            $data[$campo['nome_campo'].'categoria'] = $campo['categoria_questao'];

            //id
            //$this->addHiddenInput($campo['nome_campo'], true);

            //label
            $this->genericTextArea($campo['nome_campo'].'label', '* Título:', false, false, true, 0, 2000, 'width: 100%;');

            //categoria_questao
            $this->_addDropdown($campo['nome_campo'].'categoria', '* Categoria:', true, $categoriaQuestao);

            //aparecer
            $this->_addRadio($campo['nome_campo'].'aparecer', '* Mostrar campo na avaliação?', true, array('S' => 'Sim', 'N' => 'Não'));

            //obrigatorio
            $this->_addRadio($campo['nome_campo'].'obrigatorio', '* Campo obrigatório?', true, array('S' => 'Sim', 'N' => 'Não'));
            
        }

        //setar dados do formulário
        $this->setData($data);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
