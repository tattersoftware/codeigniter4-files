<?php namespace Tatter\Files\Database\Seeds;

use Tatter\Settings\Models\SettingModel;

class FileSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		// Check for the filesFormat template
		if (! model(SettingModel::class)->where('name', 'filesFormat')->first())
		{
			model(SettingModel::class)->insert([
				'name'      => 'filesFormat',
				'scope'     => 'user',
				'content'   => 'cards',
				'protected' => 0,
				'summary'   => 'Default file index display format',
			]);
		}
	}
}