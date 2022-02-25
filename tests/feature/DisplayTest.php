<?php

use Tatter\Files\Models\FileModel;
use Tests\Support\FeatureTestCase;

/**
 * @internal
 */
final class DisplayTest extends FeatureTestCase
{
    protected $refresh    = true;
    protected $refreshVfs = true;

    public function testNoFiles()
    {
        $result = $this->get('files');

        $result->assertStatus(200);
        $result->assertSee('No files to display');
    }

    public function testDefaultDisplaysCards()
    {
        $file = fake(FileModel::class);

        $result = $this->get('files');

        $result->assertStatus(200);
        $result->assertSee($file->filename);
    }

    public function testDataUsesPreferences()
    {
        preference('Files.sort', 'type');
        preference('Files.order', 'asc');
        preference('Files.format', 'cards');

        $file   = fake(FileModel::class);
        $result = $this->get('files');
        $result->assertStatus(200);
        $result->assertSee($file->filename);
    }

    public function provideFormat()
    {
        return [
            ['cards', 'cards'],
            ['list', 'list'],
            ['select', 'select'],
            ['invalid', config('Files')->format],
        ];
    }

    /**
     * @dataProvider provideFormat
     */
    public function testFormat(string $format, string $configFormat)
    {
        $_REQUEST['format'] = $format;

        fake(FileModel::class);
        $result = $this->get('files');

        $result->assertStatus(200);
        $this->assertSame($configFormat, preference('Files.format'));
    }

    public function provideSort()
    {
        return [
            ['filename', 'filename'],
            ['localname', 'localname'],
            ['clientname', 'clientname'],
            ['type', 'type'],
            ['size', 'size'],
            ['thumbnail', 'filename'],
            ['invalidsort', 'filename'],
        ];
    }

    /**
     * @dataProvider provideSort
     */
    public function testSorts(string $sort, string $configSort)
    {
        $_REQUEST['sort'] = $sort;

        fake(FileModel::class);
        $result = $this->get('files');

        $result->assertStatus(200);
        $this->assertSame($configSort, preference('Files.sort'));
    }

    public function provideOrder()
    {
        return [
            ['asc', 'asc'],
            ['desc', 'desc'],
            ['invalid', 'asc'],
        ];
    }

    /**
     * @dataProvider provideOrder
     */
    public function testOrders(string $order, string $configOrder)
    {
        $_REQUEST['order'] = $order;

        fake(FileModel::class);
        $result = $this->get('files');

        $result->assertStatus(200);
        $this->assertSame($configOrder, preference('Files.order'));
    }

    public function provideSearch()
    {
        return [
            ['Heathcote'],
            ['will never be found'],
        ];
    }

    /**
     * @dataProvider provideSearch
     */
    public function testSearches(string $keyword)
    {
        $_REQUEST['search'] = $keyword;

        fake(FileModel::class);
        $result = $this->get('files');

        $result->assertStatus(200);
        $content = $result->response()->getBody();

        if (strpos($content, $keyword) !== false) {
            $result->assertSee($keyword);
        } else {
            $result->assertSee('You have no files!');
        }
    }

    public function testPages()
    {
        for ($i = 0; $i < 2; $i++) {
            $file = fake(FileModel::class);
        }

        // Make a file to be sorted last
        $file = fake(FileModel::class, [
            'filename' => 'ZZZZZZZZZ',
        ]);

        // Last file should be on the next page
        $_REQUEST['perPage'] = 2;
        $result              = $this->get('files');

        $result->assertStatus(200);
        $result->assertDontSee($file->filename);

        $_GET['page'] = 2;
        $result       = $this->get('files');
        $result->assertStatus(200);
        $result->assertSee($file->filename);
    }
}
