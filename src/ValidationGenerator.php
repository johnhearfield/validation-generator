<?php

namespace GillidandaWeb\ValidationGenerator;

use DB;

class ValidationGenerator
{
    protected $schemaManager;
    
    public function __construct($schemaManager = Null)
    {
        $this->schemaManager = $schemaManager ? : 
            DB::connection()->getDoctrineSchemaManager();
            
    }
}