<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Status extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($ativo) {
        
        if($ativo == 'S') {
            return 'Ativo';
        }else{
            return 'Inativo';
        }
        
    }
}