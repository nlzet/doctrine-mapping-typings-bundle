<?php

declare(strict_types=1);

/*
 * (c) Niels Verbeek <niels@kreable.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nlzet\DoctrineMappingTypingsBundle\Tests\DependencyInjection;

use Nlzet\DoctrineMappingTypings\Typings\GeneratorConfig;
use Nlzet\DoctrineMappingTypings\Typings\ModelTypingGeneratorInterface;
use Nlzet\DoctrineMappingTypingsBundle\Command\AboutCommand;
use Nlzet\DoctrineMappingTypingsBundle\Command\ConvertCommand;
use Nlzet\DoctrineMappingTypingsBundle\DependencyInjection\NlzetDoctrineMappingTypingsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NlzetDoctrineMappingTypingsBundleTest extends TestCase
{
    public function testLoadConfig(): void
    {
        $container = new ContainerBuilder();
        $extension = new NlzetDoctrineMappingTypingsExtension();
        $container->registerExtension($extension);

        $config = [
            'exclude_patterns' => ['Pattern'],
            'class_replacements' => ['Entity' => '', 'X' => 'Y'],
            'class_aliases' => ['AcmeExample' => 'Example'],
            'only_exposed' => true,
            'always_optional' => true,
            'treat_optional_as_nullable' => true,
            'treat_nullable_as_optional' => true,
        ];
        $extension->load([$config], $container);

        // check container configuration
        static::assertTrue($container->hasDefinition(GeneratorConfig::class));
        static::assertTrue($container->hasDefinition(AboutCommand::class));
        static::assertTrue($container->hasDefinition(ConvertCommand::class));
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.exclude_patterns'), ['Pattern']);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.class_replacements'), ['Entity' => '', 'X' => 'Y']);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.class_aliases'), ['AcmeExample' => 'Example']);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.only_exposed'), true);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.always_optional'), true);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.treat_optional_as_nullable'), true);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.treat_nullable_as_optional'), true);

        // start compiled tests
        $container->compile();

        // public services
        static::assertTrue($container->has(GeneratorConfig::class));
        static::assertTrue($container->has(ModelTypingGeneratorInterface::class));

        // private services
        static::assertFalse($container->has(ConvertCommand::class));
        static::assertFalse($container->has(AboutCommand::class));

        // load actual services
        $generator = $container->get(ModelTypingGeneratorInterface::class);
        static::assertInstanceOf(ModelTypingGeneratorInterface::class, $generator);
        static::assertInstanceOf(GeneratorConfig::class, $generator->getGeneratorConfig());

        // compare output configuration
        $config = $generator->getGeneratorConfig();
        static::assertSame($config->getExcludePatterns(), ['Pattern']);
        static::assertSame($config->getClassReplacements(), ['Entity' => '', 'X' => 'Y']);
        static::assertSame($config->getClassAliases(), ['AcmeExample' => 'Example']);
        static::assertTrue($config->isOnlyExposed());
        static::assertTrue($config->isAlwaysOptional());
        static::assertTrue($config->isTreatOptionalAsNullable());
        static::assertTrue($config->isTreatNullableAsOptional());
    }
}
