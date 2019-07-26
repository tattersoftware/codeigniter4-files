<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MaintainFiles extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'maintain:files';
    protected $description = 'Removes orphaned files and their references.';

    public function run(array $params)
    {	
		CLI::write("Sample from `write` command");
	}
}
