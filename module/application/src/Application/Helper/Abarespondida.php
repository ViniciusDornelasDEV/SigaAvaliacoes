<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Abarespondida extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($aba) {
        if(empty($aba['id_agendamento'])){
            return false;
         }

         if(empty($aba['id_comercial'])){
            return false;
         }

         if(empty($aba['id_processo'])){
            return false;
         }

         if(empty($aba['id_qualidade'])){
            return false;
         }

         if(empty($aba['id_seguranca'])){
            return false;
         }

         if(empty($aba['id_ata'])){
            return false;
         }

         return true;
    }
}