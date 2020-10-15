<?php

use Tests\Support\FeatureTestCase;
use Tests\Support\Fakers\FileFaker;
use Tatter\Permits\Traits\PermitsTrait;

class PermissionTest extends FeatureTestCase
{
	use PermitsTrait;

	protected $table = 'permits';
	protected $mode = 04664;

	public function testEveryoneFullAccess()
	{
		$user = $this->login();
		$this->mayList($user->id);

		$result = $this->get('files');
		$result->assertStatus(200);
	}
}
