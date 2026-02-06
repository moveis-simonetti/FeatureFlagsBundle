<?php

namespace DZunke\FeatureFlagsBundle\Toggle\Conditions;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class Percentage extends AbstractCondition implements ConditionInterface
{

    const BASIC_COOKIE_NAME = '84a0b3f187a1d3bfefbb51d4b93074b1e5d9102a';

    const BASIC_PERCENTAGE = 100;

    const BASIC_LIFETIME = 86400;

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param mixed $config
     * @param null $argument
     * @return bool
     * @throws \Exception
     */

    public function validate($config, $argument = null): bool
    {
        $config = $this->formatConfig($config);
        $request = $this->requestStack->getMainRequest();

        if ($request?->cookies->has($config['cookie'])) {
            return (bool) $request->cookies->get($config['cookie']);
        }

        $value = (int) ($this->generateRandomNumber() < $config['percentage']);

        $request?->attributes->set(
            '_feature_flag_cookie',
            new Cookie(
                $config['cookie'],
                (string)$value,
                time() + $config['lifetime']
            )
        );

        return (bool) $value;
    }

    private function formatConfig($config)
    {
        if (!isset($config['percentage'])) {
            throw new \Exception('there must be a percentage set to use the condition');
        }

        if (!isset($config['cookie'])) {
            $config['cookie'] = self::BASIC_COOKIE_NAME;
        }

        if (!isset($config['lifetime'])) {
            $config['lifetime'] = self::BASIC_LIFETIME;
        }

        return $config;
    }

    /**
     * @return int
     */
    private function generateRandomNumber()
    {
        return 100 * (mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'percentage';
    }

}
