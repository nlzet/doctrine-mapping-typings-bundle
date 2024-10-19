<?php

declare(strict_types=1);

/*
 * (c) Niels Verbeek <niels@kreable.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nlzet\DoctrineMappingTypingsBundle\DependencyInjection;

use Nlzet\DoctrineMappingTypings\Typings\GeneratorConfig;
use Nlzet\DoctrineMappingTypings\Typings\ModelTypingGenerator;
use Nlzet\DoctrineMappingTypings\Typings\ModelTypingGeneratorInterface;
use Nlzet\DoctrineMappingTypingsBundle\Command\AboutCommand;
use Nlzet\DoctrineMappingTypingsBundle\Command\ConvertCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NlzetDoctrineMappingTypingsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $excludePatterns = $config['exclude_patterns'] ?? [];
        $classAliases = $config['class_aliases'] ?? [];
        $classReplacements = $config['class_replacements'] ?? [];
        $onlyExposed = $config['only_exposed'] ?? false;
        $alwaysOptional = $config['always_optional'] ?? false;
        $treatOptionalAsNullable = $config['treat_optional_as_nullable'] ?? false;
        $treatNullableAsOptional = $config['treat_nullable_as_optional'] ?? false;

        $container->setParameter('nlzet_doctrine_mapping_typings.exclude_patterns', $excludePatterns);
        $container->setParameter('nlzet_doctrine_mapping_typings.class_aliases', $classAliases);
        $container->setParameter('nlzet_doctrine_mapping_typings.class_replacements', $classReplacements);
        $container->setParameter('nlzet_doctrine_mapping_typings.only_exposed', $onlyExposed);
        $container->setParameter('nlzet_doctrine_mapping_typings.always_optional', $alwaysOptional);
        $container->setParameter('nlzet_doctrine_mapping_typings.treat_optional_as_nullable', $treatOptionalAsNullable);
        $container->setParameter('nlzet_doctrine_mapping_typings.treat_nullable_as_optional', $treatNullableAsOptional);

        $container->register(GeneratorConfig::class)
            ->setPublic(true)
            ->addMethodCall('setExcludePatterns', ['%nlzet_doctrine_mapping_typings.exclude_patterns%'])
            ->addMethodCall('setClassAliases', ['%nlzet_doctrine_mapping_typings.class_aliases%'])
            ->addMethodCall('setClassReplacements', ['%nlzet_doctrine_mapping_typings.class_replacements%'])
            ->addMethodCall('setOnlyExposed', ['%nlzet_doctrine_mapping_typings.only_exposed%'])
            ->addMethodCall('setAlwaysOptional', ['%nlzet_doctrine_mapping_typings.always_optional%'])
            ->addMethodCall('setTreatOptionalAsNullable', ['%nlzet_doctrine_mapping_typings.treat_optional_as_nullable%'])
            ->addMethodCall('setTreatNullableAsOptional', ['%nlzet_doctrine_mapping_typings.treat_nullable_as_optional%']);
        $container->register(ModelTypingGeneratorInterface::class, ModelTypingGenerator::class)
            ->setPublic(true)
            ->addArgument(new Reference(GeneratorConfig::class));

        $aboutCommand = $container->register(AboutCommand::class);
        $aboutCommand->setArguments([
            new Reference('doctrine.orm.entity_manager'),
            new Reference(ModelTypingGeneratorInterface::class),
        ]);
        $aboutCommand->addTag('console.command');

        $convertCommand = $container->register(ConvertCommand::class);
        $convertCommand->setArguments([
            new Reference('doctrine.orm.entity_manager'),
            new Reference(ModelTypingGeneratorInterface::class),
        ]);
        $convertCommand->addTag('console.command');
    }
}
