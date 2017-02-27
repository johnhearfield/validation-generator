<?php

namespace GillidandaWeb\ValidationGenerator;

use DB;
use PDO;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\DBALException;

class ValidationGenerator
{
    protected $schemaManager;
    
    public function __construct($schemaManager = Null)
    {
        $this->schemaManager = $schemaManager ? : 
            DB::connection()->getDoctrineSchemaManager();
            
    }
    
    public function getValidationRules($table = null, $column = null)
    {
        if ($table) {
            
        } else {
            $tables = collect($this->schemaManager->listTableNames())->slice(1);
        }
        
        $rules = $tables->map(function ($tableName) {
           return $this->getTableValidationRules($tableName);
        });
        
        dd($rules);
    }
    
    public function getTableValidationRules($tableName)
    {
        try {
            $columns = collect($this->schemaManager->listTableColumns($tableName));

            $rules = $columns->map(function ($column) {
                return $this->getColumnRules($column); 
            });
            
            return $rules;
        } catch (DBALException $e) {
            return [];
        }
    }
    
    private function getColumnRules(Column $column)
    {
        $rules = [];
        
        /*
          13 => "getType"
  14 => "getLength"
  15 => "getPrecision"
  16 => "getScale"
  17 => "getUnsigned"
  18 => "getFixed"
  19 => "getNotnull"
  20 => "getDefault"
  21 => "getPlatformOptions"
  22 => "hasPlatformOption"
  23 => "getPlatformOption"
  24 => "getColumnDefinition"
  25 => "getAutoincrement"
  26 => "setAutoincrement"
  27 => "setComment"
  28 => "getComment"
  29 => "setCustomSchemaOption"
  30 => "hasCustomSchemaOption"
  31 => "getCustomSchemaOption"
  32 => "setCustomSchemaOptions"
  33 => "getCustomSchemaOptions"
  34 => "toArray"
  35 => "isInDefaultNamespace"
  36 => "getNamespaceName"
  37 => "getShortestName"
  38 => "getFullQualifiedName"
  39 => "isQuoted"
  40 => "getName"
  41 => "getQuotedName"
        */
        
        $rules['raw'] = $column->toArray();
        
        // assume no rules
        if ($column->getAutoincrement()) {
            return $rules; 
        } 
        
        if ($column->getNotnull()) {
            $rules['required'] = null; 
        } else {
            $rules['nullable'] = null; 
        }
        
        switch ($column->getType()->getBindingType()) {
            case PDO::PARAM_INT:
                $rules['integer'] = null;
                break;
                
            case PDO::PARAM_BOOL:
                $rules['boolean'] = null;
                break;
                
            case PDO::PARAM_STR:
                switch(get_class($column->getType())) {
                    case 'Doctrine\DBAL\Types\DateTimeType':
                        dd($column);
                        break;
                }
                $rules['max'] = $column->getLength();
                $rules['type'] = $column->getType();
                break;
                
                
                
                   
        }
        
        return $rules;
        dd($column->getType()->getBindingType(), $rules);
    }
}