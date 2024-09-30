<?php

declare(strict_types=1);

/*
 * (c) Niels Verbeek <niels@kreable.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nlzet\DoctrineMappingTypingsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nlzet\DoctrineMappingTypings\Doctrine\EntityReader;
use Nlzet\DoctrineMappingTypings\Typings\GeneratorConfig;
use Nlzet\DoctrineMappingTypings\Typings\ModelTypingGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @psalm-suppress UnusedClass
 */
class ConvertCommand extends Command
{
    use ArrayInputTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GeneratorConfig $generatorConfig,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('nlzet:doctrine-typings:convert')
            ->setDescription('Convert doctrine entities into typescript typings')
            ->addArgument('destination', InputArgument::OPTIONAL, 'The output filepath', './output/doctrine-mapping-typings.ts')
            ->addOption('exclude-patterns', null, InputArgument::IS_ARRAY, 'Exclude patterns')
            ->addOption('class-aliases', null, InputArgument::IS_ARRAY, 'Class aliases')
            ->addOption('class-replacements', null, InputArgument::IS_ARRAY, 'Namespace aliases')
            ->addOption('only-exposed', null, InputArgument::OPTIONAL, 'Only exposed properties', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ([] !== $this->inputToArray($input->getOption('exclude-patterns'))) {
            $this->generatorConfig->setExcludePatterns($this->inputToArray($input->getOption('exclude-patterns')));
        }
        if ([] !== $this->inputToArray($input->getOption('class-aliases'))) {
            $this->generatorConfig->setClassAliases($this->inputToArray($input->getOption('class-aliases')));
        }
        if ([] !== $this->inputToArray($input->getOption('class-replacements'))) {
            $this->generatorConfig->setClassReplacements($this->inputToArray($input->getOption('class-replacements')));
        }
        $this->generatorConfig->setOnlyExposed((bool) $input->getOption('only-exposed'));

        $entityReader = new EntityReader($this->generatorConfig, $this->entityManager);
        $destination = ''.$input->getArgument('destination');
        $allMetadata = $entityReader->getEntities();

        $outputs = [];
        foreach ($allMetadata as $metadata) {
            $properties = $entityReader->getProperties($metadata->getName());
            $generator = new ModelTypingGenerator($this->generatorConfig, $metadata, $properties);

            $outputs[] = $generator->generate();
        }

        $outputContent = implode(\PHP_EOL, $outputs);
        $outputDir = \dirname($destination);
        // @codeCoverageIgnoreStart
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        // @codeCoverageIgnoreEnd

        file_put_contents($destination, $outputContent);

        $io->success(\sprintf('File has been written to %s', $destination));

        return Command::SUCCESS;
    }
}
