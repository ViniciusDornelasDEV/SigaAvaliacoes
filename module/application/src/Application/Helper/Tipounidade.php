<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Tipounidade extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($matriz) {
        
        if($matriz == 'S') {
            return 'Matriz';
        }else{
            return 'Filial';
        }
        
    }
}