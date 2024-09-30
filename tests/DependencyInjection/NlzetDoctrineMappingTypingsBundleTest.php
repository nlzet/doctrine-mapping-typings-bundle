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
        ];
        $extension->load([$config], $container);

        static::assertTrue($container->hasDefinition(GeneratorConfig::class));
        static::assertTrue($container->hasDefinition(ConvertCommand::class));

        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.exclude_patterns'), ['Pattern']);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.class_replacements'), ['Entity' => '', 'X' => 'Y']);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.class_aliases'), ['AcmeExample' => 'Example']);
        static::assertSame($container->getParameter('nlzet_doctrine_mapping_typings.only_exposed'), true);
    }
}
