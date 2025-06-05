<?php

namespace Microsoft\Graph\Core\Core\Test\Middleware;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Microsoft\Graph\Core\GraphConstants;
use Microsoft\Graph\Core\GraphClientFactory;
use Microsoft\Graph\Core\Middleware\GraphMiddleware;
use Microsoft\Graph\Core\Middleware\Option\GraphTelemetryOption;
use Microsoft\Kiota\Http\Middleware\Options\CompressionOption;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GraphTelemetryHandlerTest extends TestCase
{
    private $expectedSdkVersionValue;

    protected function setUp(): void
    {
        $this->expectedSdkVersionValue = "graph-php-core/".GraphConstants::SDK_VERSION
                                            .", (featureUsage=0x00000000; hostOS=".php_uname('s')
                                            ."; runtimeEnvironment=PHP/".phpversion().")";
    }

    public function testHandlerSetsCorrectHeaderByDefault()
    {
        $mockResponse = [
            function (RequestInterface $request) {
                $this->assertTrue($request->hasHeader('client-request-id'));
                $this->assertTrue($request->hasHeader('SdkVersion'));
                $this->assertEquals($this->expectedSdkVersionValue, $request->getHeaderLine('SdkVersion'));
                return new Response(200);
            }
        ];
        $this->executeMockRequestWithGraphTelemetryHandler($mockResponse);
    }

    public function testHandlerSetsCorrectServiceLibraryVersions()
    {
        $mockResponse = [
            function (RequestInterface $request) {
                $expected = 'graph-php/2.0.0, '.$this->expectedSdkVersionValue;
                $this->assertTrue($request->hasHeader('client-request-id'));
                $this->assertTrue($request->hasHeader('SdkVersion'));
                $this->assertEquals($expected, $request->getHeaderLine('SdkVersion'));
                return new Response(200);
            }
        ];
        $this->executeMockRequestWithGraphTelemetryHandler($mockResponse, new GraphTelemetryOption('v1.0', '2.0.0'));
        $mockResponse = [
            function (RequestInterface $request) {
                $expected = 'graph-php-beta/2.0.0, '.$this->expectedSdkVersionValue;
                $this->assertTrue($request->hasHeader('client-request-id'));
                $this->assertTrue($request->hasHeader('SdkVersion'));
                $this->assertEquals($expected, $request->getHeaderLine('SdkVersion'));
                return new Response(200);
            }
        ];
        $this->executeMockRequestWithGraphTelemetryHandler($mockResponse, new GraphTelemetryOption('beta', '2.0.0'));
    }

    public function testRequestOptionsOverride()
    {
        $telemetryOption = new GraphTelemetryOption();
        $telemetryOption->setClientRequestId("abcd");
        $requestOptions = [
            GraphTelemetryOption::class => $telemetryOption
        ];
        $mockResponse = [
            function (RequestInterface $request) {
                $this->assertTrue($request->hasHeader('client-request-id'));
                $this->assertEquals("abcd", $request->getHeaderLine('client-request-id'));
                $this->assertTrue($request->hasHeader('SdkVersion'));
                $this->assertEquals($this->expectedSdkVersionValue, $request->getHeaderLine('SdkVersion'));
                return new Response(200);
            }
        ];
        $this->executeMockRequestWithGraphTelemetryHandler($mockResponse, null, $requestOptions);
    }

    public function testCorrectFeatureFlagsSetByDefaultHandlerStack()
    {
        $featureFlag = sprintf('0x%08X', 0x00000000 | 0x00000002);
        $expectedSdkVersionValue = "graph-php-core/".GraphConstants::SDK_VERSION
            .", (featureUsage={$featureFlag}; hostOS=".php_uname('s')
            ."; runtimeEnvironment=PHP/".phpversion().")";
        $mockResponse = [
            function (RequestInterface $request) use ($expectedSdkVersionValue) {
                $this->assertTrue($request->hasHeader('SdkVersion'));
                $this->assertEquals($expectedSdkVersionValue, $request->getHeaderLine('SdkVersion'));
                $this->assertEquals('/me/messages', $request->getUri()->getPath());
                return new Response(200);
            }
        ];
        $mockHandler = new MockHandler($mockResponse);
        $guzzleClient = GraphClientFactory::createWithMiddleware(GraphClientFactory::getDefaultHandlerStack($mockHandler));
        $guzzleClient->get("/users/me-token-to-replace/messages");
    }

    private function executeMockRequestWithGraphTelemetryHandler(array $mockResponses, ?GraphTelemetryOption $graphTelemetryOption = null, array $requestOptions = [])
    {
        $mockHandler = new MockHandler($mockResponses);
        $handlerStack = new HandlerStack($mockHandler);
        $handlerStack->push(GraphMiddleware::graphTelemetry($graphTelemetryOption));

        $guzzleClient = GraphClientFactory::createWithMiddleware($handlerStack);
        return $guzzleClient->get("/", $requestOptions);
    }
    /**
     * @throws GuzzleException
     */
    public function testRequestOptionsOverrideForCompression(): void
    {
        $compressionOption = new CompressionOption([CompressionOption::gzip()]);
        $requestOptions = [
            CompressionOption::class => $compressionOption,
            'body' => Utils::streamFor("{Some JSOn}")
        ];
        $mockResponse = [
            function (RequestInterface $request) {
                $this->assertTrue($request->hasHeader('Content-Encoding'));
                $this->assertEquals("gzip", $request->getHeaderLine('Content-Encoding'));
                return new Response(200);
            }
        ];
        $this->executeMockRequestWithCompressionHandler($mockResponse, $compressionOption, $requestOptions);
    }
    /**
     * @param array<mixed> $mockResponses
     * @param CompressionOption|null $compressionOption
     * @param array<string, mixed> $requestOptions
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function executeMockRequestWithCompressionHandler(array $mockResponses, ?CompressionOption $compressionOption = null, array $requestOptions = []): ResponseInterface
    {
        $mockHandler = new MockHandler($mockResponses);
        $handlerStack = new HandlerStack($mockHandler);
        $handlerStack->push(GraphMiddleware::compression($compressionOption));

        $guzzleClient = GraphClientFactory::createWithMiddleware($handlerStack);
        return $guzzleClient->post("/", $requestOptions);
    }
}
