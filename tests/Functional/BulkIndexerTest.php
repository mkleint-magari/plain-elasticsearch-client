<?php
/**
 *
 * @author Markus Kleint <markus.kleint@gmail.com>
 * @since  17.07.17
 * PHP 5.5
 *
 */

namespace PlainEsClient\Tests\Functional;

use PHPUnit\Framework\TestCase;
use PlainEsClient\BulkIndexer;
use PlainEsClient\Client;

class BulkIndexerTest extends TestCase
{

    private $testHost = 'localhost';

    private $testPort = 9200;

    private $testIndexName = 'test-index';


    protected function setUp()
    {
        parent::setUp();

        $elasticSocket = $this->testHost. ':' . $this->testPort;

        exec('curl --silent -XDELETE "' . $elasticSocket . '/test-index"');
        exec('curl --silent -XPUT "' . $elasticSocket . '/test-index"');

        exec(
            'curl --silent -XPUT "' . $elasticSocket . '/test-index/_mapping/test-type" -d \'
{
    "test-type" : {
        "properties" : {
            "sparePartId" : {"type" : "string" }
        }
    }
}
\''
        );
    }

    public function testIndex()
    {
        $testDocumentCount = 10000;

        $bulkIndexer = new BulkIndexer(5000, $this->getClient());


        for ($i = 0; $i < $testDocumentCount; $i++) {

            $testDocument = $this->getTestDocument($i);
            $bulkIndexer->add($this->testIndexName, 'sparePart', json_decode($testDocument, true), '123_abss234_' . $i);
        }

        $bulkIndexer->flush();
        $this->getClient()->refresh($this->testIndexName);

        exec('curl --silent localhost:9200/test-index/sparePart/_count', $output);
        $countResponse = implode(PHP_EOL, $output);

        static::assertStringStartsWith(
            '{"count":' . $testDocumentCount . ',',
            $countResponse,
            'wrong number of documents in testindex after BulkIndexer::index-call'
        );

    }

    private function getClient()
    {
        return new Client($this->testHost, $this->testPort);
    }

    /**
     * @return string
     */
    private function getTestDocument($id)
    {
        return /* @lang JSON */<<<JSON
{
    "id": "123_abss234_{$id}",
    "sparePartId": "123_abss234",
    "sparePartId2": "123_abss234",
    "sparePartId3": "123_abss234",
    "sparePartId4": "123_abss234",
    "sparePartId5": "123_abss234",

    "articleVehicles" : [{
        "vehicle" : {
            "kTypNr"  : 0,
            "hsnTsns" : ["0603547", "0600828"]
        }
    }],

    "articleVehicles2" : [{
        "vehicle" : {
            "kTypNr"  : 1,
            "hsnTsns" : ["0603547", "0600828"]
        }
    }],

    "articleVehicles3" : [{
        "vehicle" : {
            "kTypNr"  : 2,
            "hsnTsns" : ["0603547", "0600828"]
        }
    }]
}
JSON;

    }

    protected function tearDown()
    {

        $elasticSocket = $this->testHost . ':' . $this->testPort;
        //exec('curl --silent -XDELETE "' . $elasticSocket . '/test-index"');

        parent::tearDown();
    }
}