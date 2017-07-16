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

        exec('curl --silent -XPUT "'.$elasticSocket.'/test-index"');

        exec('curl --silent -XPUT "'.$elasticSocket.'/test-index/_mapping/test-type" -d \'
{
    "test-type" : {
        "properties" : {
            "title" : {"type" : "string" }
        }
    }
}
\'');

    }

    /**
     * @dataProvider provideTestSearch
     */
    public function testSearch($query, $index, $type)
    {
        $client = new Client($this->elasticTestHost, $this->elasticTestPort);
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
                $element,
                $response,
                'expected element ['.$element.'] not found in response'
            );
        }
    }

    public function provideTestSearch() {

        return [
            ['query' => '', 'index' => '', 'type' => ''],
            ['query' => '', 'index' => 'test-index', 'type' => ''],
            ['query' => '', 'index' => 'test-index', 'type' => 'test-type'],
            ['query' => '{}', 'index' => 'test-index', 'type' => 'test-type'],
        ];
    }

    protected function tearDown()
    {

        $elasticSocket = $this->elasticTestHost . ':' . $this->elasticTestPort;
        exec('curl --silent -XPUT "'.$elasticSocket.'/test-index"');

        parent::tearDown();
    }
}