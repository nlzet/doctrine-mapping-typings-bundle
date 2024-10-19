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
use Nlzet\DoctrineMappingTypings\Typings\ModelTypingGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @psalm-suppress UnusedClass
 */
class AboutCommand extends Command
{
    use ArrayInputTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ModelTypingGenerator $modelTypingGenerator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('nlzet:doctrine-typings:about')
            ->setDescription('Convert doctrine entities into typescript typings')
            ->addOption('exclude-patterns', null, InputArgument::IS_ARRAY, 'Exclude patterns')
            ->addOption('class-aliases', null, InputArgument::IS_ARRAY, 'Class aliases')
            ->addOption('class-replacements', null, InputArgument::IS_ARRAY, 'Namespace aliases')
            ->addOption('only-exposed', null, InputArgument::OPTIONAL, 'Only exposed properties', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $generatorConfig = $this->modelTypingGenerator->getGeneratorConfig();
        if ([] !== $this->inputToArray($input->getOption('exclude-patterns'))) {
            $generatorConfig->setExcludePatterns($this->inputToArray($input->getOption('exclude-patterns')));
        }
        if ([] !== $this->inputToArray($input->getOption('class-aliases'))) {
            $generatorConfig->setClassAliases($this->inputToArray($input->getOption('class-aliases')));
        }
        if ([] !== $this->inputToArray($input->getOption('class-replacements'))) {
            $generatorConfig->setClassReplacements($this->inputToArray($input->getOption('class-replacements')));
        }
        $generatorConfig->setOnlyExposed((bool) $input->getOption('only-exposed'));

        $entityReader = new EntityReader($generatorConfig, $this->entityManager);
        $allMetadata = $entityReader->getEntities();

        $io = new SymfonyStyle($input, $output);
        $io->title(\sprintf('These %d entities are mapped/filtered, and will output the following typings:', \count($allMetadata)));

        $table = new Table($output);
        $table->setHeaders(['Entity', 'Target typing']);
        foreach ($allMetadata as $metadata) {
            $table->addRow([
                $metadata->getName(),
                $this->modelTypingGenerator->getClassAlias($metadata->getName()),
            ]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
