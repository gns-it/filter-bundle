<?php


namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy;


use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Configuration
{
    /**
     * @var ParameterBag
     */
    private $options;
    /**
     * @var HandlerStrategyInterface
     */
    private $context;

    public function __construct(HandlerStrategyInterface $context, array $options = [], array $defaults = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults($defaults);
        $this->options = new ParameterBag($resolver->resolve($options));
        $this->context = $context;
    }

    /**
     * @param $name
     * @param $arguments
     * @return string|mixed
     */
    public function __call($name, $arguments)
    {
        return $this->options->$name(...$arguments);
    }

    /**
     * @return HandlerStrategyInterface
     */
    public function getContext(): ?HandlerStrategyInterface
    {
        return $this->context;
    }
}