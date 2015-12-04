<?php

namespace OpenSkill\Datatable\Queries;


use Illuminate\Http\Request;
use OpenSkill\Datatable\Columns\ColumnConfigurationBuilder;
use OpenSkill\Datatable\Columns\Orderable\Orderable;
use OpenSkill\Datatable\Columns\Searchable\Searchable;
use OpenSkill\Datatable\Queries\Parser\Datatable19QueryParser;

class DT19QueryParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var Datatable19QueryParser */
    private $parser;

    /** @var Request */
    private $request;

    /**
     * Will set up a the parser to test
     */
    protected function setUp()
    {
        // create request
        $this->request = new Request([
            'sEcho' => 13,
            'iDisplayStart' => 11,
            'iDisplayLength' => 103,
            'iColumns' => 1, // will be ignored, the column number is already set on the server side
            'sSearch' => 'fooBar',
            'bRegex' => true,
            'bSearchable_1' => true, // will be ignored, the configuration is already set on the server side
            'sSearch_1' => 'fooBar_1',
            'bRegex_1' => true, // will be ignored, the configuration is already set on the server side
            'bSortable_1' => true, // will be ignored, the configuration is already set on the server side
            'iSortingCols' => 1, // will be ignored, the configuration is already set on the server side
            'iSortCol_1' => true,
            'sSortDir_1' => 'desc',
        ]);

        $this->parser = new Datatable19QueryParser($this->request);
    }

    /**
     * Will test if the query parser can parse the request params for datatable 1.9
     * http://legacy.datatables.net/usage/server-side
     *
     */
    public function testCorrectParsing()
    {
        // create columnconfiguration
        $column = ColumnConfigurationBuilder::create()
            ->name("fooBar")
            ->build();

        $conf = $this->parser->parse([$column]);

        $this->assertSame(13, $conf->drawCall());
        $this->assertSame(11, $conf->start());
        $this->assertSame(103, $conf->length());
        $this->assertSame('fooBar', $conf->searchValue());
        $this->assertTrue($conf->isGlobalRegex());


        // assert column search
        $this->assertCount(1, $conf->searchColumns());
        $def = $conf->searchColumns()['fooBar'];
        $this->assertSame("fooBar_1", $def);

        // assert column order
        $this->assertCount(1, $conf->orderColumns());
        $def = $conf->orderColumns()['fooBar'];
        $this->assertSame("desc", $def);
    }

    /**
     * Will test if the query parser will ignore search and order advise if the columns forbid them
     *
     */
    public function testWrongParsing()
    {
        // create columnconfiguration
        $column = ColumnConfigurationBuilder::create()
            ->name("fooBar")
            ->orderable(Orderable::NONE())
            ->searchable(Searchable::NONE())
            ->build();

        $conf = $this->parser->parse([$column]);

        // assert column search
        $this->assertCount(0, $conf->searchColumns());

        // assert column order
        $this->assertCount(0, $conf->orderColumns());
    }
}
