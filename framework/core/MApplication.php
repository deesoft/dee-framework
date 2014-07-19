<?php

/**
 * Description of MApplication
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class MApplication extends MObject
{
    
    public function __construct($config = array())
    {
        Mdm::$app = $this;
                
        parent::__construct($config);
    }

    public function run()
    {
        
    }
    
    protected function handleRequest()
    {
        
    }
}