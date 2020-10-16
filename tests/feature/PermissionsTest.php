<?php

use Myth\Auth\Entities\User;
use Tatter\Permits\Models\PermitModel;
use Tests\Support\FeatureTestCase;
use Tests\Support\Fakers\FileFaker;

class PermissionsTest extends FeatureTestCase
{
	/**
	 * A User with files and no special permissions
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * A User with files and global read access
	 *
	 * @var User
	 */
	protected $super;

	/**
	 * A User with files and full access
	 *
	 * @var User
	 */
	protected $admin;

	/**
	 * A User without files but list access
	 *
	 * @var User
	 */
	protected $proctor;

	/**
	 * Creates some test files and users with different permissions.
	 */
	protected function setUp(): void
	{
		parent::setUp();

		// Create Users with their Files
		$this->user    = $this->createUserWithFiles();
		$this->super   = $this->createUserWithFiles();
		$this->admin   = $this->createUserWithFiles();
		$this->proctor = $this->createUserWithFiles([], 0);

		// Set permissions
		model(PermitModel::class)->insertBatch([
			[
				'name'    => 'listFiles',
				'user_id' => $this->proctor->id,
			],
			[
				'name'    => 'listFiles',
				'user_id' => $this->super->id,
			],
			[
				'name'    => 'readFiles',
				'user_id' => $this->super->id,
			],
			[
				'name'    => 'adminFiles',
				'user_id' => $this->admin->id,
			],
		]);
	}

	//--------------------------------------------------------------------

	public function testDefaultAccessListsAllFiles()
	{
		$this->assertEquals(04660, $this->model->getMode());
		$result = $this->get('files');
		$result->assertStatus(200);

		$files = $this->model->getForUser($this->super->id);
		$result->assertSee($files[0]->filename);

		$files = $this->model->getForUser($this->admin->id);
		$result->assertSee($files[0]->filename);
	}

	public function testDenyListRedirects()
	{
		$this->setMode(00660);
		$result = $this->get('files');

		$result->assertStatus(302);
		$result->assertSessionHas('error', lang('Permits.notPermitted'));
	}

	public function testDenyAjaxReturnsError()
	{
		$this->setMode(00660);
		$result = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->get('files');

		$result->assertStatus(403);
		$result->assertJSONFragment(['error' => lang('Permits.notPermitted')]);
	}

	public function testProctorListsAllFiles()
	{
		$this->setMode(00660);
		$this->login($this->proctor->id);

		$result = $this->withSession()->get('files');
		$result->assertStatus(200);

		$files = $this->model->getForUser($this->admin->id);
		$result->assertSee($files[0]->filename);
	}

	public function testAuthenticatedListOwnOnly()
	{
		$this->setMode(00660);
		$this->login($this->proctor->id);

		$fileOwnByProctor = fake(FileFaker::class);
		model('FileModel')->addToUser($fileOwnByProctor->id, $this->proctor->id);

		$fileOwnByProctor2 = fake(FileFaker::class);
		model('FileModel')->addToUser($fileOwnByProctor2->id, $this->proctor->id);

		$fileOwnByAdmin = fake(FileFaker::class);
		model('FileModel')->addToUser($fileOwnByAdmin->id, $this->admin->id);

		$result = $this->withSession()->get('files/user/' . $this->proctor->id);
		$result->assertStatus(200);

		$files = $this->model->getForUser($this->proctor->id);
		$result->assertSee($fileOwnByProctor->filename);
		$result->assertSee($fileOwnByProctor2->filename);
		$result->assertDontSee($fileOwnByAdmin->filename);
	}

	public function provideAccess()
	{
		yield ['read' => 00444];
		yield ['write' => 00222];
		yield ['execute' => 00111];
		yield ['read write execute' => 00777];
	}

	/**
	 * @dataProvider provideAccess
	 */
	public function testAdminAccess($mode)
	{
		$this->setMode($mode);
		$this->login($this->admin->id);

		$result = $this->withSession()->get('files');
		$result->assertStatus(200);

		$files = $this->model->getForUser($this->admin->id);
		$result->assertSee($files[0]->filename);
	}
}
