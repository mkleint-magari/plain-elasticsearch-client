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

    public function testTrue()
    {
        $client = new Client();
        $response = $client->search('', '');

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
}