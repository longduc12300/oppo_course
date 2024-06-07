<?php

class ApiController extends Zend_Controller_Action
{
    
    public function init()
    {
        /* Initialize action controller here */

    }


    public function testAction()
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
    
        $data = ['info' => 'test'];
        echo json_encode($data); 
        exit; // Hoặc die();
    }
}
