<?php

namespace GillidandaWeb\ValidationGenerator;

use DB;
use Doctrine\DBAL\Schema\Index;
use PDO;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Column;

class ValidationGenerator
{
    protected $schemaManager;

    public function __construct($schemaManager = null)
    {
        $this->schemaManager = $schemaManager ?:
            DB::connection()->getDoctrineSchemaManager();
    }

    public function getValidationRules($table = null, $column = null, $isIgnoreUser = false)
    {
        if ($table) {
        } else {
            $tables = collect($this->schemaManager->listTableNames());
        }

        return $tables->mapWithKeys(function ($tableName) use ($isIgnoreUser) {
            return [$tableName => $this->getTableValidationRules($tableName, $isIgnoreUser)];
        });
    }

    public function getTableValidationRules($tableName, $isIgnoreUser)
    {
        $foreignKeyRules = [];

        $uniqueIndexRules = [];

        try {
            $columns = collect($this->schemaManager->listTableColumns($tableName));

            // Parse table indexes
            $indexes = collect($this->schemaManager->listTableIndexes($tableName));
            $indexes->map(function(Index $index) use(&$uniqueIndexRules, $tableName) {
                $columnName = array_first($index->getColumns());

                if($index->isUnique()) {
                    $uniqueIndexRules[$columnName]['unique'] =
                        sprintf('%s,%s', $tableName, $columnName);
                }
            });

            // Parse table foreign keys
            $foreignKeys = collect($this->schemaManager->listTableForeignKeys($tableName));
            $foreignKeys->map(function($foreignKey) use(&$foreignKeyRules) {
                $foreignKeyRules[array_first($foreignKey->getLocalColumns())]['exists'] =
                    sprintf('%s,%s', $foreignKey->getForeignTableName(), $foreignKey->getForeignColumns()[0]);
            });

            $tableRules = $columns->reject(function ($column) use ($isIgnoreUser) {
                return preg_match('/\_at$/', $column->getName())
                        || ($isIgnoreUser && $column->getName() == 'user_id');
            })
                ->map(function ($column) use($foreignKeyRules, $uniqueIndexRules) {
                    return $this->getColumnRules($column, $foreignKeyRules, $uniqueIndexRules);
                });

            return $tableRules;
        } catch (DBALException $e) {
            return collect([]);
        }
    }

    private function getColumnRules(Column $column, $foreignKeyRules, $uniqueIndexRules)
    {
        $rules = collect([]);

        if(isset($foreignKeyRules[$column->getName()])) {
            $rules['exists'] = $foreignKeyRules[$column->getName()]['exists'];
        }

        if(isset($uniqueIndexRules[$column->getName()])) {
            $rules['unique'] = $uniqueIndexRules[$column->getName()]['unique'];
        }

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

        // $rules['raw'] = $column->toArray();

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

                if ($column->getUnsigned()) {
                    $rules['min'] = 0;
                }
                break;

            case PDO::PARAM_BOOL:
                $rules['boolean'] = null;
                break;

            case PDO::PARAM_STR:
                switch (get_class($column->getType())) {
                    case 'Doctrine\DBAL\Types\DateTimeType':
                    case 'Doctrine\DBAL\Types\DateType':
                        $rules['date'] = null;
                        break;

                    default:
                        $rules['max'] = $column->getLength();
                }
                break;

        }

        // guess by name
        if (strstr($column->getName(), 'email')) {
            $rules['email'] = null;
        }

        if (strstr($column->getName(), 'url')) {
            $rules['url'] = null;
        }

        if (strstr($column->getName(), 'slug')) {
            $rules['regex'] = '/^[a-z0-9]+(\_-[a-z0-9]+)*$/';
        }

        return $rules;
    }
}
