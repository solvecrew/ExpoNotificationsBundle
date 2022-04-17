<?php

namespace Solvecrew\ExpoNotificationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    // Defaults:
    const EXPO_API_ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sc_expo_notifications');
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->children()
                ->scalarNode('expo_api_endpoint')->defaultValue(self::EXPO_API_ENDPOINT)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
