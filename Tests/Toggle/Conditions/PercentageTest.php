<?php

namespace DZunke\FeatureFlagsBundle\Tests\Toggle\Conditions;

use DZunke\FeatureFlagsBundle\Toggle\Conditions\AbstractCondition;
use DZunke\FeatureFlagsBundle\Toggle\Conditions\ConditionInterface;
use DZunke\FeatureFlagsBundle\Toggle\Conditions\Percentage;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PercentageTest extends TestCase
{
    /**
     * @var RequestStack
     */
    private $requestStackMock;

    public function setUp() : void
    {
        $requestMock = $this->createMock(Request::class);
        $this->requestStackMock = $this->createMock(RequestStack::class);
        $this->requestStackMock->method('getMainRequest')->willReturn($requestMock);

    }

    public function testItExtendsCorrectly()
    {
        $sut = new Percentage($this->requestStackMock);

        $this->assertInstanceOf(AbstractCondition::class, $sut);
        $this->assertInstanceOf(ConditionInterface::class, $sut);
    }

    public function testItThrowsExceptionWhenPercentageIsNotSet()
    {
        $this->expectException(Exception::class);

        $sut = new Percentage($this->requestStackMock);
        $sut->validate([], 'nothing');
    }
    public function testItReturnsTrueWhenCookieIsAlreadySet(): void
    {
        $request = new Request(
            cookies: [
                'percentage' => 1,
            ]
        );

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock
            ->method('getMainRequest')
            ->willReturn($request);

        $sut = new Percentage($requestStackMock);

        $this->assertTrue(
            $sut->validate([
                'percentage' => 798,
            ])
        );
    }

    public function testItReturnsBoolWhenCookieIsNotSet(): void
    {
        $request = new Request(cookies: []);

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock
            ->method('getMainRequest')
            ->willReturn($request);

        $sut = new Percentage($requestStackMock);

        self::assertIsBool(
            $sut->validate([
                'percentage' => 3,
            ])
        );
    }

    public function testToString()
    {
        $sut = new Percentage($this->requestStackMock);

        $this->assertSame('percentage', (string) $sut);
    }

}
