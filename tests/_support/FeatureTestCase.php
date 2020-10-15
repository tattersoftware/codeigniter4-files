<?php namespace Tests\Support;

use CodeIgniter\Config\Factories;
use Config\Services;
use Tatter\Files\Models\FileModel;

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

	/**
	 * Injects a permission mode into the shared FileModel.
	 *
	 * @param int $mode Octal mode
	 */
	protected function setMode(int $mode)
	{
		$model = new FileModel();
		$model->setMode($mode);

		Factories::injectMock('models', FileModel::class, $model);
	}
}
