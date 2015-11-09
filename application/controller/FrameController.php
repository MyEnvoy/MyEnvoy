<?php

use Famework\Controller\Famework_Controller;
use Famework\View\Famework_View_Frame_Controller;

class FrameController extends Famework_Controller implements Famework_View_Frame_Controller {

    public function init() {
        
    }

    public function renderTop() {
        ?>
        <h1>MyEnvoy</h1>
        <?php
    }

    public function renderBottom() {
        ?>
        <footer>Copyright 2015 Fabian Maier</footer>
        <?php
    }

}
