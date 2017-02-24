<?php

namespace GillidandaWeb\ValidationGenerator\Console\Commands;

use Illuminate\Console\Command;
use DB;

class GenerateValidationRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:validation {--t|table? : Limit to table} {--c|column? : Limit to column}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate validation rules from db schema';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
