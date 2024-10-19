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
use Nlzet\DoctrineMappingTypings\Typings\ModelTypingGenerator;
use Nlzet\DoctrineMappingTypingsBundle\Command\AboutCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

require_once __DIR__.'/../../vendor/nlzet/doctrine-mapping-typings/tests/Util/DoctrineConfigurationFactory.php';

class AboutCommandTest extends TestCase
{
    public static function provideInputs(): \Iterator
    {
        yield 'option defaults' => [
            [
            ],
            3,
            [
                'Nlzet\DoctrineMappingTypings\Tests\Fixture\Entity\Address' => 'NlzetDoctrineMappingTypingsTestsFixtureEntityAddress',
                'Nlzet\DoctrineMappingTypings\Tests\Fixture\Entity\ExamplePropertyTypes' => 'NlzetDoctrineMappingTypingsTestsFixtureEntityExamplePropertyTypes',
                'Nlzet\DoctrineMappingTypings\Tests\Fixture\Entity\Person' => 'NlzetDoctrineMappingTypingsTestsFixtureEntityPerson',
            ],
        ];
        yield 'configured options' => [
            [
                '--class-replacements' => 'NlzetDoctrineMappingTypingsTestsFixture=Nlzet,Entity=',
                '--exclude-patterns' => 'ExamplePropertyTypes',
                '--class-aliases' => 'NlzetDoctrineMappingTypingsTestsFixtureEntityAddress=CustomAddress',
                '--only-exposed' => true,
            ],
            2,
            [
                'Nlzet\DoctrineMappingTypings\Tests\Fixture\Entity\Address' => 'CustomAddress',
                'Nlzet\DoctrineMappingTypings\Tests\Fixture\Entity\Person' => 'NlzetPerson',
                'Nlzet\DoctrineMappingTypings\Tests\Fixture\Entity\ExamplePropertyTypes' => false,
            ],
        ];
    }

    #[DataProvider('provideInputs')]
    public function testRunAboutCommands(array $input, int $numMapped, array $expectedMapping): void
    {
        $generatorConfig = new GeneratorConfig();
        $doctrineFactory = new DoctrineConfigurationFactory();
        $configuration = $doctrineFactory->createConfiguation();
        $entityManager = $doctrineFactory->createEntityManager($configuration);
        $generator = new ModelTypingGenerator($generatorConfig);
        $aboutCommand = new AboutCommand($entityManager, $generator);

        $cmdOuput = new BufferedOutput();
        try {
            $aboutCommand->run(new ArrayInput($input), $cmdOuput);
        } catch (\Exception $e) {
            static::fail($e->getMessage());
        }

        $stdOut = $cmdOuput->fetch();

        static::assertStringContainsString('| Entity', $stdOut);
        static::assertStringContainsString('| Target typing', $stdOut);
        static::assertStringContainsString(\sprintf('These %d entities are mapped/filtered, and will output the following typings:', $numMapped), $stdOut);

        foreach ($expectedMapping as $entity => $targetTyping) {
            if (false === $targetTyping) {
                static::assertStringNotContainsString($entity, $stdOut);
            } else {
                /** @var string $regexSafeClassname */
                $regexSafeClassname = str_replace('\\', '\\\\', $entity);

                static::assertMatchesRegularExpression('#\|\s+'.$regexSafeClassname.'\s+\|\s+'.$targetTyping.'\s+\|\n#m', $stdOut);
            }
        }
    }
}
