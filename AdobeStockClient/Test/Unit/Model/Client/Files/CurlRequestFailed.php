<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeStockClient\Test\Unit\Model\Client\Files;

use Magento\AdobeIms\Model\Config as ImsConfig;
use Magento\AdobeImsApi\Api\GetAccessTokenInterface;
use Magento\AdobeStockClient\Model\Client\Files;
use Magento\AdobeStockClient\Model\Config as ClientConfig;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Exception as WebApiException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test case when a cru request failed
 */
class CurlRequestFailed extends TestCase
{
    /**
     * @var ImsConfig|MockObject
     */
    private $imsConfigMock;

    /**
     * @var ClientConfig|MockObject
     */
    private $clientConfigMock;

    /**
     * @var Resolver|MockObject
     */
    private $localeResolverMock;

    /**
     * @var GetAccessTokenInterface|MockObject
     */
    private $getAccessTokenMock;

    /**
     * @var Files|MockObject
     */
    private $files;

    /**
     * @var Curl|MockObject
     */
    private $curlAdapterMock;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->imsConfigMock = $this->createMock(ImsConfig::class);
        $this->clientConfigMock = $this->createMock(ClientConfig::class);
        $this->localeResolverMock = $this->createMock(Resolver::class);
        $this->getAccessTokenMock = $this->createMock(GetAccessTokenInterface::class);
        $jsonMock = $this->createMock(Json::class);
        $curlFactoryMock = $this->createMock(CurlFactory::class);

        $this->files = (new ObjectManager($this))->getObject(
            Files::class,
            [
                'imsConfig' => $this->imsConfigMock,
                'clientConfig' => $this->clientConfigMock,
                'localeResolver' => $this->localeResolverMock,
                'getAccessToken' => $this->getAccessTokenMock,
                'curlFactory' => $curlFactoryMock,
                'json' => $jsonMock,
            ]
        );

        $this->curlAdapterMock = $this->createMock(Curl::class);
        $curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->curlAdapterMock);
    }

    /**
     * Test case when curl request failed
     *
     * @param string $xProductName
     * @param string $apiKey
     * @param string $accessToken
     *
     * @dataProvider curlRequestHeaders
     */
    public function testCurlRequestFailed(string $xProductName, string $apiKey, string $accessToken): void
    {
        $ids = [1];
        $columns = [];
        $locale = 'en';

        $this->clientConfigMock->expects($this->once())
            ->method('getProductName')
            ->willReturn($xProductName);

        $this->imsConfigMock->expects($this->once())
            ->method('getApiKey')
            ->willReturn($apiKey);

        $this->getAccessTokenMock->expects($this->once())
            ->method('execute')
            ->willReturn($accessToken);

        $headers = [
            'x-Product' => $xProductName,
            'x-api-key' => $apiKey,
            'Authorization' => 'Bearer ' . $accessToken
        ];
        $this->curlAdapterMock->expects($this->once())
            ->method('setHeaders')
            ->with($headers)
            ->willReturnSelf();

        $this->curlAdapterMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->clientConfigMock->expects($this->once())
            ->method('getFilesUrl')
            ->willReturn('string');

        $this->curlAdapterMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(400);

        $this->expectException(WebApiException::class);
        $this->files->execute($ids, $columns, $locale);
    }

    /**
     * Data provider for the curl request to the Adobe authentication service
     *
     * @return array
     */
    public function curlRequestHeaders(): array
    {
        return
            [
                [
                    'Magento/dev-2.3-develop',
                    '75y87d439eqweqw4f4asde64ae42060fc571c456sdfsdqwe',
                    'Bearer ' . base64_encode('Magento/dev-2.3-develop')
                ]
            ];
    }
}
