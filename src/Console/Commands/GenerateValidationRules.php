<?php

namespace GillidandaWeb\ValidationGenerator\Console\Commands;

use Illuminate\Console\Command;
use GillidandaWeb\ValidationGenerator\ValidationGenerator;

class GenerateValidationRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:validation {table?} {column?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate validation rules from db schema';
    
    public $generator;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ValidationGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
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
        dump('done', $table, $column);
    }
}
