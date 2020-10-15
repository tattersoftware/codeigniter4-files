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
				'name'    => 'mayList',
				'user_id' => $this->proctor->id,
			],
			[
				'name'    => 'mayList',
				'user_id' => $this->super->id,
			],
			[
				'name'    => 'mayRead',
				'user_id' => $this->super->id,
			],
			[
				'name'    => 'mayAdmin',
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
}
