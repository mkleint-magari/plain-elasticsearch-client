<?php

namespace PlainEsClient\Tests\Functional;

use PHPUnit\Framework\TestCase;
use PlainEsClient\Client;

/**
 *
 * @author Markus Kleint <markus.kleint@gmail.com>
 * @since  16.07.17
 * PHP 5.5
 *
 */
class ClientTest extends TestCase
{

    private $elasticTestHost = '127.0.0.1';

    private $elasticTestPort = 9200;

    protected function setUp()
    {
        parent::setUp();

        $elasticSocket = $this->elasticTestHost . ':' . $this->elasticTestPort;

        exec('curl --silent -XPUT "' . $elasticSocket . '/test-index"');

        exec(
            'curl --silent -XPUT "' . $elasticSocket . '/test-index/_mapping/test-type" -d \'
{
    "test-type" : {
        "properties" : {
            "title" : {"type" : "string" }
        }
    }
}
\''
        );
    }

    /**
     * @dataProvider provideTestSearch
     *
     * @param string $query
     * @param string $index
     * @param string $type
     */
    public function testSearch($query, $index, $type)
    {
        $client   = new Client($this->elasticTestHost, $this->elasticTestPort);
        $response = $client->search($query, $index, $type);

        $expectedElementsInResponse = [
            '{"took":',
            '"timed_out":',
            '"_shards":',
            '{"total":',
            '"successful":',
            '"failed":',
            '"hits":',
        ];

        foreach ($expectedElementsInResponse as $element) {

            static::assertContains(
                $element, $response, 'expected element [' . $element . '] not found in response'
            );
        }
    }

    /**
     * @param string $query
     * @param string $index
     * @param string $type
     *
     * @dataProvider provideTestSearch
     */
    public function testInitiateScan($query, $index, $type)
    {
        $client = new Client($this->elasticTestHost, $this->elasticTestPort);

        $response = $client->initiateScan($query, $index, '1m', $type);

        $expectedElementsInResponse = [
            '{"_scroll_id":',
            '"took":',
            '"timed_out":',
            '"_shards":',
            '{"total":',
            '"successful":',
            '"failed":',
            '"hits":',
        ];

        foreach ($expectedElementsInResponse as $element) {

            static::assertContains(
                $element, $response, 'expected element [' . $element . '] not found in response'
            );
        }
    }

    /**
     * @param string $query
     * @param string $index
     * @param string $type
     *
     * @dataProvider  provideTestSearch
     */
    public function testScan($query, $index, $type)
    {
        //scrolId must be initiated first
        $client = new Client();
        $response = $client->initiateScan($query, $index, '1m', $type);

        $arrResponse = json_decode($response, true);

        $scrollId = $arrResponse['_scroll_id'];

        $scannedResponse = $client->scan($scrollId);

        $expectedElementsInResponse = [
            '{"_scroll_id":',
            '"took":',
            '"timed_out":',
            '"_shards":',
            '{"total":',
            '"successful":',
            '"failed":',
            '"hits":',
        ];

        foreach ($expectedElementsInResponse as $element) {

            static::assertContains(
                $element,
                $scannedResponse,
                'expected element [' . $element . '] not found in response with scrollId ['.$scrollId.']'
            );
        }
    }

    public function provideTestSearch()
    {

        return [
            'everything empty' => ['query' => '', 'index' => '', 'type' => ''],
            'index name given only' => ['query' => '', 'index' => 'test-index', 'type' => ''],
            'index and type given' => ['query' => '', 'index' => 'test-index', 'type' => 'test-type'],
            'index, type and empty query' => ['query' => '{}', 'index' => 'test-index', 'type' => 'test-type'],
        ];
    }

    protected function tearDown()
    {

        $elasticSocket = $this->elasticTestHost . ':' . $this->elasticTestPort;
        exec('curl --silent -XPUT "' . $elasticSocket . '/test-index"');

        parent::tearDown();
    }
}