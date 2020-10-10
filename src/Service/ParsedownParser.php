<?php

namespace App\Service;

use Parsedown;

class ParsedownParser{

    public function __construct(){
        $this->parseDown = new Parsedown();
    }

    public function markdownToHtml($markdown){
        return $this->parseDown->text($markdown);
    }

}