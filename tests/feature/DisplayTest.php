<?php

use Tests\Support\Fakers\FileFaker;
use Tests\Support\FeatureTestCase;

/**
 * @internal
 */
final class DisplayTest extends FeatureTestCase
{
    public function testNoFiles()
    {
        $result = $this->get('files');

        $result->assertStatus(200);
        $result->assertSee('You have no files');
    }

    public function testDefaultDisplaysCards()
    {
        $file = fake(FileFaker::class);

        $result = $this->get('files');

        $result->assertStatus(200);
        $result->assertSee($file->filename);
    }

    public function testDataUsesPreferences()
    {
        preference('Files.sort', 'type');
        preference('Files.order', 'asc');
        preference('Files.format', 'cards');

        $file   = fake(FileFaker::class);
        $result = $this->get('files');
        $result->assertStatus(200);
        $result->assertSee($file->filename);
    }

    public function provideFormat()
    {
        yield ['cards', 'cards'];

        yield ['list', 'list'];

        yield ['select', 'select'];

        yield ['invalid', config('Files')->format];
    }

    /**
     * @dataProvider provideFormat
     */
    public function testFormat(string $format, string $configFormat)
    {
        $_REQUEST['format'] = $format;

        $file   = fake(FileFaker::class);
        $result = $this->get('files');

        $result->assertStatus(200);
        $this->assertSame($configFormat, preference('Files.format'));
    }

    public function provideSort()
    {
        yield ['filename', 'filename'];

        yield ['localname', 'localname'];

        yield ['clientname', 'clientname'];

        yield ['type', 'type'];

        yield ['size', 'size'];

        yield ['thumbnail', 'thumbnail'];

        yield ['invalidsort', 'filename'];
    }

    /**
     * @dataProvider provideSort
     */
    public function testSorts(string $sort, string $configSort)
    {
        $_REQUEST['sort'] = $sort;

        $file   = fake(FileFaker::class);
        $result = $this->get('files');

        $result->assertStatus(200);
        $this->assertSame($configSort, preference('Files.sort'));
    }

    public function provideOrder()
    {
        yield ['asc', 'asc'];

        yield ['desc', 'desc'];

        yield ['invalid', 'asc'];
    }

    /**
     * @dataProvider provideOrder
     */
    public function testOrders(string $order, string $configOrder)
    {
        $_REQUEST['order'] = $order;

        $file   = fake(FileFaker::class);
        $result = $this->get('files');

        $result->assertStatus(200);
        $this->assertSame($configOrder, preference('Files.order'));
    }

    public function provideSearch()
    {
        yield ['Heathcote'];

        yield ['will never be found'];
    }

    /**
     * @dataProvider provideSearch
     */
    public function testSearches(string $keyword)
    {
        $_REQUEST['search'] = $keyword;

        $file   = fake(FileFaker::class);
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
        for ($i = 0; $i < 12; $i++) {
            $file = fake(FileFaker::class);
        }

        // Make a file to be sorted last
        $file = fake(FileFaker::class, [
            'filename' => 'ZZZZZZZZZ',
        ]);

        // Last file should be on the next page
        $result = $this->get('files');
        $result->assertStatus(200);
        $result->assertDontSee($file->filename);

        $_GET['page'] = 2;
        $result       = $this->get('files');
        $result->assertStatus(200);
        $result->assertSee($file->filename);
    }
}
