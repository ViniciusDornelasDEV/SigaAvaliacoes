<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Caractervazio extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($dado) {
        if(empty($dado)){
            return '-';
         }else{
         	return $dado;
         }
    }
}