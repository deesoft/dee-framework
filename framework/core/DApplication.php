<?php

/**
 * Description of MApplication
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DApplication extends DObject
{
    
    public function __construct($config = array())
    {
        Dee::$app = $this;
                
        parent::__construct($config);
    }

    
    public function run()
    {
        
    }
    
    protected function handleRequest()
    {
        
    }
}