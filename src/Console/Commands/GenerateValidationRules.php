<?php

namespace GillidandaWeb\ValidationGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use GillidandaWeb\ValidationGenerator\ValidationGenerator;

class GenerateValidationRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:validation {table?} {column?} {--output=controller} {--ignoreuser}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate validation rules from db schema';
    
    public $generator;
    public $files;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files, ValidationGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
        $this->files = $files;
    }

    /**
     * Get the validate request in controller stub file for the generator.
     *
     * @return string
     */
    protected function getControllerValidateStub()
    {
        return $this->getStubsDir() . '/controller-validate.stub';
    }

    /**
     * Get the form request stub file for the generator.
     *
     * @return string
     */
    protected function getFormRequestStub()
    {
        return $this->getStubsDir() . '/form-request.stub';
    }
    
    /**
     * Get the directory that contains our stubs.
     *
     * @return string
     */
    private function getStubsDir()
    {
        return __DIR__ . '/../../../stubs';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->argument('table');
        $column = $this->argument('column');
        $output = $this->option('output');
        $isIgnoreUser = $this->option('ignoreuser');
        
        if (!in_array($output, ['formrequest', 'controller'])) {
            throw new \Exception('Invalid output option');
        }
        
        switch ($output) {
            case 'formrequest':
                $stubPath = $this->getFormRequestStub();
                break;
                
            case 'controller':
                $stubPath = $this->getControllerValidateStub();
                break;
        }
        
        if ($table  && $column) {
            if ($column) {
                var_export($this->generator->getValidationRules($table, $column)->toArray());
                return;
            }
            
            var_export($this->generator->getValidationRules($table)->toArray());
            return;
        }
        
        $this->generator->getValidationRules(null, null, $isIgnoreUser)->each(function ($rules, $tableName) use ($stubPath, $isIgnoreUser) {
            $this->buildRules($stubPath, $rules, $tableName, $isIgnoreUser);
        });
        
        return;
    }
    
    /**
     * Build the rules.
     *
     * @param  string  $stubPath
     * @param  Collection  $rules
     * @param  string  $tableName
     * @return GenerateValidationRules
     */
    protected function buildRules($stubPath, $rules, $tableName)
    {
        $stub = $this->files->get($stubPath);
        
        $rulesOutput = $rules->transform(function ($columnRules) {
            return $columnRules->map(function($ruleValue, $ruleKey) {

                $str = $ruleKey;
                
                if (!is_null($ruleValue)) {
                    $str .= ':' . $ruleValue; 
                }

                return $str;
            })->implode('|');
        })->filter(function ($columnRules) {
            return $columnRules;
        })->map(function ($columnRulesText, $columnName) {
            return "'$columnName' => '$columnRulesText',";   
        })->implode("\r\n            ");

        return $this->replaceRules($stub, $rulesOutput, $tableName); // $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the rules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceRules(&$stub, $rules, $tableName)
    {
        $stub = str_replace('DummyRules', $rules, $stub);
        $stub = str_replace('DummyTableName', $tableName, $stub);

        $this->info($tableName . "\n");
        $this->line($stub . "\n\n");

        return $this;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace'],
            [$this->getNamespace($name), $this->rootNamespace()],
            $stub
        );

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyClass', $class, $stub);
    }
}
