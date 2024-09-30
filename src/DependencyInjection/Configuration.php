<?php

declare(strict_types=1);

/*
 * (c) Niels Verbeek <niels@kreable.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nlzet\DoctrineMappingTypingsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nlzet_doctrine_mapping_typings');

        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('exclude_patterns')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('class_aliases')
                    ->useAttributeAsKey('search')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('class_replacements')
                    ->useAttributeAsKey('search')
                    ->scalarPrototype()->end()
                ->end()
                ->booleanNode('only_exposed')->defaultFalse()->end()
            ->end();

        return $treeBuilder;
    }
}
