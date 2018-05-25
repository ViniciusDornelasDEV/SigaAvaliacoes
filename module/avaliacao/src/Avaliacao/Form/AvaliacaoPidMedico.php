<?php
 namespace Avaliacao\Form;
 
 use Application\Form\Base as BaseForm; 

 class AvaliacaoPidMedico extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $campos)
    {
        parent::__construct($name);

        $campos = parent::prepararCamposDinamicos($campos);
        if($campos['pontos_positivos']['aparecer'] == 'S'){
            $obrigatorio = $this->booleanByAtivo($campos['pontos_positivos']['obrigatorio']);
            $this->genericTextArea('pontos_positivos', $obrigatorio['prefixo'].$campos['pontos_positivos']['label'], $obrigatorio['obrigatorio'], false, true, 0, 2000, 'width: 100%');   
        } 

        if($campos['pontos_desenvolver']['aparecer'] == 'S'){
            $obrigatorio = $this->booleanByAtivo($campos['pontos_desenvolver']['obrigatorio']);
            $this->genericTextArea('pontos_desenvolver', $obrigatorio['prefixo'].$campos['pontos_desenvolver']['label'], $obrigatorio['obrigatorio'], false, true, 0, 2000, 'width: 100%');   
        }

        if($campos['plano_acao']['aparecer'] == 'S'){
            $obrigatorio = $this->booleanByAtivo($campos['plano_acao']['obrigatorio']);
            $this->genericTextArea('plano_acao', $obrigatorio['prefixo'].$campos['plano_acao']['label'], $obrigatorio['obrigatorio'], false, true, 0, 2000, 'width: 100%');   
        } 

        if($campos['desenvolvimento_semestral']['aparecer'] == 'S'){
            $obrigatorio = $this->booleanByAtivo($campos['desenvolvimento_semestral']['obrigatorio']);
            $this->genericTextArea('desenvolvimento_semestral', $obrigatorio['prefixo'].$campos['desenvolvimento_semestral']['label'], $obrigatorio['obrigatorio'], false, true, 0, 2000, 'width: 100%');   
        }

        if($campos['desenvolvimento_anual']['aparecer'] == 'S'){
            $obrigatorio = $this->booleanByAtivo($campos['desenvolvimento_anual']['obrigatorio']);
            $this->genericTextArea('desenvolvimento_anual', $obrigatorio['prefixo'].$campos['desenvolvimento_anual']['label'], $obrigatorio['obrigatorio'], false, true, 0, 2000, 'width: 100%');   
        }

        if($campos['feedback_informal']['aparecer'] == 'S'){
            $obrigatorio = $this->booleanByAtivo($campos['feedback_informal']['obrigatorio']);
            $this->genericTextArea('feedback_informal', $obrigatorio['prefixo'].$campos['feedback_informal']['label'], $obrigatorio['obrigatorio'], false, true, 0, 2000, 'width: 100%');   
        }

        if($campos['meta_medio_prazo']['aparecer'] == 'S'){
            $obrigatorio = $this->booleanByAtivo($campos['meta_medio_prazo']['obrigatorio']);
            $this->genericTextArea('meta_medio_prazo', $obrigatorio['prefixo'].$campos['meta_medio_prazo']['label'], $obrigatorio['obrigatorio'], false, true, 0, 2000, 'width: 100%');   
        }     

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
