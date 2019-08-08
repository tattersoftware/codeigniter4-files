<?php namespace Tatter\Files\Database\Seeds;

use Tatter\Settings\Models\SettingModel;

class FileSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		// Check for format setting
		$settings = new SettingModel();
		$setting = $settings->where('name', 'fileFormat')->first();
		if (empty($setting)):
			// No setting - add the template
			$row = [
				'name'       => 'filesFormat',
				'scope'      => 'user',
				'content'    => 'cards',
				'protected'  => 0,
				'summary'    => 'Default file index display format',
			];

			$settings->save($row);			
		endif;
	}
}