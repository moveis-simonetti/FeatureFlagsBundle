<?php

namespace Tests\Integration;

use DZunke\FeatureFlagsBundle\Subscriber\FeatureFlagCookieSubscriber;
use DZunke\FeatureFlagsBundle\Toggle\Conditions\Percentage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class PercentageCookieIntegrationTest extends TestCase
{
    private RequestStack $requestStack;
    private Percentage $percentage;
    private FeatureFlagCookieSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->percentage = new Percentage($this->requestStack);
        $this->subscriber = new FeatureFlagCookieSubscriber();
    }

    public function testItSetsCookieWhenCookieDoesNotExist(): void
    {
        $request = new Request();
        $this->requestStack->push($request);

        $config = [
            'percentage' => 100,
            'cookie' => '_feature_flag_cookie',
            'lifetime' => 3600,
        ];

        $result = $this->percentage->validate($config);

        self::assertTrue($result);

        self::assertTrue($request->attributes->has('_feature_flag_cookie'));

        $cookie = $request->attributes->get('_feature_flag_cookie');
        self::assertInstanceOf(Cookie::class, $cookie);
        self::assertSame('_feature_flag_cookie', $cookie->getName());
        self::assertSame('1', $cookie->getValue());

        $response = new Response();

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $this->subscriber->onKernelResponse($event);

        $cookies = $response->headers->getCookies();
        self::assertCount(1, $cookies);
        self::assertSame('_feature_flag_cookie', $cookies[0]->getName());
    }

    public function testItReturnsCookieValueWhenCookieAlreadyExists(): void
    {
        $request = new Request();
        $request->cookies->set('_feature_flag_cookie', '1');
        $this->requestStack->push($request);

        $config = [
            'percentage' => 0,
            'cookie' => '_feature_flag_cookie',
        ];

        $result = $this->percentage->validate($config);

        self::assertTrue($result);

        self::assertFalse($request->attributes->has('_feature_flag_cookie'));

        $response = new Response();

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $this->subscriber->onKernelResponse($event);

        self::assertCount(0, $response->headers->getCookies());
    }
}
