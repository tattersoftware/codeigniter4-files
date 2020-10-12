<?php

use CodeIgniter\Config\Config;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use Tatter\Files\Controllers\Files;
use Tatter\Files\Entities\File;
use Tatter\Files\Exceptions\FilesException;
use Tests\Support\Fakers\FileFaker;
use Tests\Support\FilesTestCase;
use Tests\Support\Models\FileModel;

class ControllerTest extends FilesTestCase
{
	use \CodeIgniter\Test\ControllerTester;

	/**
	 * Our Controller set by the trait
	 *
	 * @var Files|null
	 */
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();

		$this->controller = null;
	}

	public function testThrowsWithInvalidStoragePath()
	{
		$this->config->storagePath = HOMEPATH . 'README.md';

		$this->expectException(FilesException::class);
		$this->expectExceptionMessage(lang('Files.dirFail', [$this->config->storagePath]));

		$controller = new Files($this->config);
	}

	//--------------------------------------------------------------------

	public function testCreatesMissingStoragePath()
	{
		$this->config->storagePath .= 'subdirectory/';

		$controller = new Files($this->config);

		$this->assertDirectoryExists($this->config->storagePath);
		$this->assertDirectoryIsWritable($this->config->storagePath);
	}

	//--------------------------------------------------------------------

	public function testGetSortUsesInput()
	{
		$_REQUEST['sort']              = 'size';
		service('settings')->filesSort = 'type';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getSort');
		$result = $method();

		$this->assertEquals('size', $result);
	}

	public function testGetSortUsesSettings()
	{
		service('settings')->filesSort = 'type';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getSort');
		$result = $method();

		$this->assertEquals('type', $result);
	}

	public function testGetSortIgnoresInvalid()
	{
		$_REQUEST['sort'] = 'foobar';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getSort');
		$result = $method();

		$this->assertEquals('filename', $result);
	}

	//--------------------------------------------------------------------

	public function testGetOrderUsesInput()
	{
		$_REQUEST['order']              = 'desc';
		service('settings')->filesOrder = 'asc';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getOrder');
		$result = $method();

		$this->assertEquals('desc', $result);
	}

	public function testGetOrderUsesSettings()
	{
		service('settings')->filesOrder = 'desc';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getOrder');
		$result = $method();

		$this->assertEquals('desc', $result);
	}

	public function testGetOrderIgnoresInvalid()
	{
		$_REQUEST['order'] = 'foobar';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getOrder');
		$result = $method();

		$this->assertEquals('asc', $result);
	}

	//--------------------------------------------------------------------

	public function testGetFormatUsesInput()
	{
		$_REQUEST['format']              = 'select';
		service('settings')->filesFormat = 'list';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getFormat');
		$result = $method();

		$this->assertEquals('select', $result);
	}

	public function testGetFormatUsesSettings()
	{
		service('settings')->filesFormat = 'list';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getFormat');
		$result = $method();

		$this->assertEquals('list', $result);
	}

	public function testGetFormatUsesConfig()
	{
		service('settings')->filesFormat = 'foobar';

		$this->config->defaultFormat = 'select';
		Config::injectMock('Files', $this->config);

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getFormat');
		$result = $method();

		$this->assertEquals('select', $result);
	}

	public function testGetFormatIgnoresInvalid()
	{
		$_REQUEST['format'] = 'foobar';

		$this->controller(Files::class);

		$method = $this->getPrivateMethodInvoker($this->controller, 'getFormat');
		$result = $method();

		$this->assertEquals('cards', $result);
	}

	public function testDataUsesVarWithFaker()
	{
		$file = fake(FileFaker::class);

		$controller = new Files();
		$controller->initController(service('request'), service('response'), service('logger'));

		$method = $this->getPrivateMethodInvoker($controller, 'setData');
		$method([
			'files' => [
				0 => $file
			],
		]);

		$result = $controller->display();
		$this->assertStringContainsString($file->filename, $result);
	}

	public function testDataUsesVarViaPassEntity()
	{
		$controller = new Files();
		$controller->initController(service('request'), service('response'), service('logger'));

		$file = new File;
		$file->filename ='foo.txt';
		$file->thumbnail = '';
		$file->type = '';
		$file->localname = '';
		$file->clientname = '';
		$file->size = 1;
		$file->created_at = new class {
			public function humanize()
			{
				return '';
			}
		};

		$method = $this->getPrivateMethodInvoker($controller, 'setData');
		$method([
			'files' => [
				0 => $file
			],
		]);

		$result = $controller->display();
		$this->assertStringContainsString($file->filename, $result);
	}
}
