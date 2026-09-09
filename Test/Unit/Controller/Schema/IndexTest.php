<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Controller\Schema;

use Magebit\AgenticCommerce\Controller\Schema\Index;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * Both names are advertised by the payment handler, so both have to resolve.
     *
     * @dataProvider advertisedNameProvider
     * @param string $name Schema name the handler advertises
     * @return void
     */
    public function testAdvertisedSchemaIsServed(string $name): void
    {
        $result = $this->controller($name)->execute();
        $data = $result->getData();

        $this->assertSame(200, $result->getHttpResponseCode());
        $this->assertIsArray($data);
        $this->assertSame('https://json-schema.org/draft/2020-12/schema', $data['$schema']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function advertisedNameProvider(): array
    {
        return [
            'config' => [Index::NAME_CONFIG],
            'card instrument' => [Index::NAME_INSTRUMENT_CARD],
        ];
    }

    /**
     * @return void
     */
    public function testCardInstrumentRequiresACredentialToken(): void
    {
        $data = $this->controller(Index::NAME_INSTRUMENT_CARD)->execute()->getData();

        $this->assertIsArray($data);
        $this->assertSame(['type', 'credential'], $data['required']);
        $this->assertSame(['type', 'token'], $data['properties']['credential']['required']);
    }

    /**
     * @return void
     */
    public function testUnknownNameIsNotFound(): void
    {
        $result = $this->controller('nope')->execute();

        $this->assertSame(404, $result->getHttpResponseCode());
    }

    /**
     * @return void
     */
    public function testMissingNameIsNotFound(): void
    {
        $result = $this->controller('')->execute();

        $this->assertSame(404, $result->getHttpResponseCode());
    }

    /**
     * @param string $name Requested schema name
     * @return Index
     */
    private function controller(string $name): Index
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->willReturn($name);

        $factory = $this->createMock(JsonFactory::class);
        $factory->method('create')->willReturnCallback(fn (): ResultJson => $this->resultJson());

        return new Index($factory, $request);
    }

    /**
     * @return ResultJson A stub that records the payload and status it is handed
     */
    private function resultJson(): ResultJson
    {
        $state = ['data' => null, 'code' => 200];

        $json = $this->getMockBuilder(ResultJson::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setData', 'setHttpResponseCode'])
            ->addMethods(['getData', 'getHttpResponseCode'])
            ->getMock();

        $json->method('setData')->willReturnCallback(function ($data) use (&$state, $json) {
            $state['data'] = $data;

            return $json;
        });
        $json->method('setHttpResponseCode')->willReturnCallback(function ($code) use (&$state, $json) {
            $state['code'] = (int)$code;

            return $json;
        });
        $json->method('getData')->willReturnCallback(function () use (&$state) {
            return $state['data'];
        });
        $json->method('getHttpResponseCode')->willReturnCallback(function () use (&$state): int {
            return $state['code'];
        });

        return $json;
    }
}
