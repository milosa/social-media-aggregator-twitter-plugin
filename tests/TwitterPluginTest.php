<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorTests\Twitter;

use Milosa\SocialMediaAggregatorBundle\Aggregator\Handler;
use Milosa\SocialMediaAggregatorBundle\Twitter\TwitterPlugin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class TwitterPluginTest extends TestCase
{
    /**
     * @var TwitterPlugin
     */
    private $plugin;

    public function setUp(): void
    {
        $this->plugin = new TwitterPlugin();
    }

    public function testGetPluginName(): void
    {
        $this->assertEquals('twitter', $this->plugin->getPluginName());
    }

    public function testGetResourcesPath(): void
    {
        $this->assertEquals(DIRECTORY_SEPARATOR.'Resources', mb_substr($this->plugin->getResourcesPath(), -10));
    }

    public function testLoad(): void
    {
        $container = $this->createContainer(false);

        $this->assertTrue($container->hasParameter('milosa_social_media_aggregator.twitter_consumer_key'));
        $this->assertTrue($container->hasParameter('milosa_social_media_aggregator.twitter_consumer_secret'));
        $this->assertTrue($container->hasParameter('milosa_social_media_aggregator.twitter_oauth_token'));
        $this->assertTrue($container->hasParameter('milosa_social_media_aggregator.twitter_oauth_token_secret'));

        $this->assertTrue($container->hasDefinition('milosa_social_media_aggregator.fetcher.twitter.abstract'));
        $this->assertTrue($container->hasDefinition('milosa_social_media_aggregator.plugin.twitter'));
        $this->assertTrue($container->hasDefinition('milosa_social_media_aggregator.handler.twitter'));

        $this->assertFalse($container->hasDefinition('milosa_social_media_aggregator.twitter_cache'));
    }

    public function testLoadWithCache(): void
    {
        $container = $this->createContainer(true);

        $this->assertTrue($container->hasDefinition('milosa_social_media_aggregator.twitter_cache'));
        $this->assertTrue($container->getDefinition('milosa_social_media_aggregator.fetcher.twitter.abstract')->hasMethodCall('setCache'));
    }

    public function testAddConfiguration(): void
    {
        $arrayNode = new ArrayNodeDefinition('node');
        $this->plugin->addConfiguration($arrayNode);
        $node = $arrayNode->getNode();

        $this->assertFalse($node->isRequired());
        $this->assertTrue($node->hasDefaultValue());
        $this->assertSame([
            'auth_data' => [
                'consumer_key' => null,
                'consumer_secret' => null,
                'oauth_token' => null,
                'oauth_token_secret' => null,
            ],
            'sources' => [],
            'enable_cache' => false,
            'cache_lifetime' => 3600,
            'template' => 'twitter.twig',
        ],
        $node->getDefaultValue());
    }

    private function createFakeConfig(bool $enableCache = false): array
    {
        return [
            'plugins' => [
                'twitter' => [
                    'auth_data' => [
                        'consumer_key' => 'fake_consumer_key',
                        'consumer_secret' => 'fake_consumer_secret',
                        'oauth_token' => 'fake_oauth_token',
                        'oauth_token_secret' => 'fake_oauth_token_secret',
                    ],
                    'enable_cache' => $enableCache,
                    'cache_lifetime' => 123,
                    'number_of_tweets' => 42,
                    'account_to_fetch' => 'NASA',
                    'image_size' => 'thumb',
                    'sources' => [
                        [
                        'search_type' => 'profile',
                        'search_term' => 'test',
                        'number_of_tweets' => 2,
                        'image_size' => 'thumb',
                            ],
                    ],
                ],
            ],
        ];
    }

    private function createFakeAggregatorDefinition(ContainerBuilder $container): void
    {
        $definition = new Definition(DummyAggregator::class);
        $container->setDefinition('milosa_social_media_aggregator.aggregator', $definition);
    }

    /**
     * @param bool $enableCache
     *
     * @return ContainerBuilder
     */
    protected function createContainer(bool $enableCache): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $this->createFakeAggregatorDefinition($container);
        $this->plugin->load($this->createFakeConfig($enableCache), $container);

        return $container;
    }
}

class DummyAggregator
{
    public function addHandler(Handler $handler)
    {
    }
}
