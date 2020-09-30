<?php

use Tatter\Files\Controllers\Files;
use Tatter\Files\Exceptions\FilesException;
use Tests\Support\FeatureTestCase;
use Tests\Support\Models\FileModel;

class IndexTest extends FeatureTestCase
{
	public function testNoFiles()
	{
		$result = $this->get('files');

		$result->assertStatus(200);
		$result->assertSee('No files to display.', 'p');
	}
}
