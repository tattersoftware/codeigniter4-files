<?php namespace Tests\Support;

use App\Entities\Card;
use App\Models\CardModel;
use CodeIgniter\Test\Fabricator;
use Config\Services;

class FeatureTestCase extends FilesTestCase
{
	use \CodeIgniter\Test\FeatureTestTrait;
	use \Myth\Auth\Test\AuthTestTrait;

	/**
	 * If present, will override application
	 * routes when using call().
	 *
	 * @var \CodeIgniter\Router\RouteCollection
	 */
	protected $routes;

	/**
	 * Values to be set in the SESSION global
	 * before running the test.
	 *
	 * @var array
	 */
	protected $session = [];

	/**
	 * Enabled auto clean op buffer after request call
	 *
	 * @var boolean
	 */
	protected $clean = true;

	protected function setUp(): void
	{
		parent::setUp();

		$this->resetAuthServices();
	}
}
