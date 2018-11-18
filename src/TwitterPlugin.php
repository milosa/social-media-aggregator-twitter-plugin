<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorBundle\Twitter;

use Milosa\SocialMediaAggregatorBundle\MilosaSocialMediaAggregatorPlugin;
use Milosa\SocialMediaAggregatorBundle\Twitter\DependencyInjection\TwitterPluginExtension;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function realpath;

class TwitterPlugin extends Bundle implements MilosaSocialMediaAggregatorPlugin
{
    public function getPluginName(): string
    {
        return 'twitter';
    }

    public function getTwigPath(): string
    {
        return realpath(__DIR__.'/../Resources/views');
    }

    public function load(array $config, ContainerBuilder $container): void
    {
        $extension = new TwitterPluginExtension();
        $extension->load($config, $container);
        $this->setContainerParameters($config, $container);
        $this->configureCaching($config, $container);
        $this->registerHandler($container);
    }

    public function addConfiguration(ArrayNodeDefinition $pluginNode): void
    {
        $pluginNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('auth_data')
                    ->addDefaultsIfNotSet()
                    ->isRequired()
                    ->children()
                        ->scalarNode('consumer_key')->defaultNull()->end()
                        ->scalarNode('consumer_secret')->defaultNull()->end()
                        ->scalarNode('oauth_token')->defaultNull()->end()
                        ->scalarNode('oauth_token_secret')->defaultNull()->end()
                    ->end()
                ->end() //auth data
                ->booleanNode('enable_cache')->defaultValue(false)->end()
                ->integerNode('cache_lifetime')->info('Cache lifetime in seconds')->defaultValue(3600)->end()
                ->integerNode('number_of_tweets')->defaultValue(10)->end()
                ->scalarNode('account_to_fetch')->isRequired()->defaultNull()->info('Screen name of the account you want to fetch the timeline of.')->end()
                ->scalarNode('template')->defaultValue('twitter.twig')->end()
                ->booleanNode('show_images')->defaultTrue()->end()
                ->booleanNode('hashtag_links')->defaultTrue()->end()
                ->enumNode('image_size')->values(['thumb', 'large', 'medium', 'small'])->defaultValue('thumb')->end()
            ->end();
    }

    public function setContainerParameters(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('milosa_social_media_aggregator.twitter_consumer_key', $config['plugins']['twitter']['auth_data']['consumer_key']);
        $container->setParameter('milosa_social_media_aggregator.twitter_consumer_secret', $config['plugins']['twitter']['auth_data']['consumer_secret']);
        $container->setParameter('milosa_social_media_aggregator.twitter_oauth_token', $config['plugins']['twitter']['auth_data']['oauth_token']);
        $container->setParameter('milosa_social_media_aggregator.twitter_oauth_token_secret', $config['plugins']['twitter']['auth_data']['oauth_token_secret']);
        $container->setParameter('milosa_social_media_aggregator.twitter_numtweets', $config['plugins']['twitter']['number_of_tweets']);
        $container->setParameter('milosa_social_media_aggregator.twitter_account', $config['plugins']['twitter']['account_to_fetch']);
        $container->setParameter('milosa_social_media_aggregator.twitter_image_size', $config['plugins']['twitter']['image_size']);
    }

    /**
     * @todo check fetcher definition
     * @todo check if this can be a abstract method?
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    public function configureCaching(array $config, ContainerBuilder $container): void
    {
        if ($config['plugins']['twitter']['enable_cache'] === true) {
            $cacheDefinition = new Definition(FilesystemAdapter::class, [
                'milosa_social',
                $config['plugins']['twitter']['cache_lifetime'],
                '%kernel.cache_dir%',
            ]);

            $container->setDefinition('milosa_social_media_aggregator.twitter_cache', $cacheDefinition)->addTag('cache.pool');
            $fetcherDefinition = $container->getDefinition('milosa_social_media_aggregator.fetcher.twitter');
            $fetcherDefinition->addMethodCall('setCache', [new Reference('milosa_social_media_aggregator.twitter_cache')]);
        }
    }

    private function registerHandler(ContainerBuilder $container): void
    {
        $aggregatorDefinition = $container->getDefinition('milosa_social_media_aggregator.aggregator');
        $aggregatorDefinition->addMethodCall('addHandler', [new Reference('milosa_social_media_aggregator.handler.twitter')]);
    }
}