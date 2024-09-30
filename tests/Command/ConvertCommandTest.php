<?php

declare(strict_types=1);

/*
 * (c) Niels Verbeek <niels@kreable.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nlzet\DoctrineMappingTypingsBundle\Tests\Command;

use Nlzet\DoctrineMappingTypings\Tests\Util\DoctrineConfigurationFactory;
use Nlzet\DoctrineMappingTypings\Typings\GeneratorConfig;
use Nlzet\DoctrineMappingTypingsBundle\Command\ConvertCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

require_once __DIR__.'/../../vendor/nlzet/doctrine-mapping-typings/tests/Util/DoctrineConfigurationFactory.php';

class ConvertCommandTest extends TestCase
{
    public static function provideInputs(): \Iterator
    {
        yield 'option defaults' => [
            [
            ],
            __DIR__.'/../__fixtures/typings-default.ts',
        ];
        yield 'configured options' => [
            [
                '--class-replacements' => 'NlzetDoctrineMappingTypingsTestsFixture=Nlzet,Entity=',
                '--exclude-patterns' => 'ExamplePropertyTypes',
                '--class-aliases' => 'NlzetDoctrineMappingTypingsTestsFixtureEntityAddress=CustomAddress',
                '--only-exposed' => true,
            ],
            __DIR__.'/../__fixtures/typings-configured.ts',
        ];
    }

    #[DataProvider('provideInputs')]
    public function testRunConvertCommands(array $input, string $fixture): void
    {
        $generatorConfig = new GeneratorConfig();
        $doctrineFactory = new DoctrineConfigurationFactory();
        $configuration = $doctrineFactory->createConfiguation();
        $entityManager = $doctrineFactory->createEntityManager($configuration);
        $convertCommand = new ConvertCommand($entityManager, $generatorConfig);
        static::assertTrue($convertCommand->getDefinition()->hasArgument('destination'));

        $cmdOuput = new BufferedOutput();
        try {
            $convertCommand->run(new ArrayInput($input), $cmdOuput);
        } catch (\Exception $e) {
            static::fail($e->getMessage());
        }

        static::assertStringContainsString('File has been written to ', $cmdOuput->fetch());

        $outputContent = file_get_contents($fixture);
        $fixtureContent = file_get_contents($fixture);
        static::assertSame($fixtureContent, $outputContent);
    }
}
