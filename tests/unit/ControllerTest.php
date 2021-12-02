<?php

use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\Test\ControllerTestTrait;
use Tatter\Files\Controllers\Files;
use Tatter\Files\Entities\File;
use Tatter\Files\Exceptions\FilesException;
use Tests\Support\Fakers\FileFaker;
use Tests\Support\FilesTestCase;

/**
 * @internal
 */
final class ControllerTest extends FilesTestCase
{
    use ControllerTestTrait;

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
        $this->config->storagePath = realpath(HOMEPATH . 'README.md') ?: HOMEPATH . 'README.md';

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
        $_REQUEST['sort'] = 'size';
        preference('Files.sort', 'type');

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getSort');
        $result = $method();

        $this->assertSame('size', $result);
    }

    public function testGetSortUsesPreference()
    {
        preference('Files.sort', 'type');

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getSort');
        $result = $method();

        $this->assertSame('type', $result);
    }

    public function testGetSortIgnoresInvalid()
    {
        $_REQUEST['sort'] = 'foobar';
        preference('Files.sort', 'bambaz');

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getSort');
        $result = $method();

        $this->assertSame('filename', $result);
    }

    //--------------------------------------------------------------------

    public function testGetOrderUsesInput()
    {
        $_REQUEST['order'] = 'desc';
        preference('Files.order', 'asc');

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getOrder');
        $result = $method();

        $this->assertSame('desc', $result);
    }

    public function testGetOrderUsesPreference()
    {
        preference('Files.order', 'desc');

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getOrder');
        $result = $method();

        $this->assertSame('desc', $result);
    }

    public function testGetOrderIgnoresInvalid()
    {
        $_REQUEST['order'] = 'foobar';

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getOrder');
        $result = $method();

        $this->assertSame('asc', $result);
    }

    //--------------------------------------------------------------------

    public function testGetFormatUsesInput()
    {
        $_REQUEST['format'] = 'select';
        preference('Files.format', 'list');

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getFormat');
        $result = $method();

        $this->assertSame('select', $result);
    }

    public function testGetFormatUsesPreference()
    {
        preference('Files.format', 'list');

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getFormat');
        $result = $method();

        $this->assertSame('list', $result);
    }

    public function testGetFormatIgnoresInvalid()
    {
        $_REQUEST['format'] = 'foobar';

        $this->controller(Files::class);

        $method = $this->getPrivateMethodInvoker($this->controller, 'getFormat');
        $result = $method();

        $this->assertSame('cards', $result);
    }

    public function testDataUsesVarWithFaker()
    {
        $file = fake(FileFaker::class);

        $controller = new Files();
        $controller->initController(service('request'), service('response'), service('logger'));

        $method = $this->getPrivateMethodInvoker($controller, 'setData');
        $method([
            'files' => [
                0 => $file,
            ],
        ]);

        $result = $controller->display();
        $this->assertStringContainsString($file->filename, $result);
    }

    public function testDataUsesVarViaPassEntity()
    {
        $controller = new Files();
        $controller->initController(service('request'), service('response'), service('logger'));

        $file             = new File();
        $file->filename   = 'foo.txt';
        $file->thumbnail  = '';
        $file->type       = '';
        $file->localname  = '';
        $file->clientname = '';
        $file->size       = 1;
        $file->created_at = new class () {
            public function humanize()
            {
                return '';
            }
        };

        $method = $this->getPrivateMethodInvoker($controller, 'setData');
        $method([
            'files' => [
                0 => $file,
            ],
        ]);

        $result = $controller->display();
        $this->assertStringContainsString($file->filename, $result);
    }

    //--------------------------------------------------------------------

    public function testExportCreatesRecord()
    {
        $file = fake(FileFaker::class, [
            'localname' => 'image.jpg',
        ]);

        $this->controller(Files::class);
        $result = $this->execute('export', 'download', $file->id);

        $this->assertInstanceOf(DownloadResponse::class, $result->response());
        $this->seeInDatabase('exports', ['file_id' => $file->id]);
    }
}
