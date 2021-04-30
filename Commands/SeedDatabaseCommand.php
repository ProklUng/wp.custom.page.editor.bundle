<?php

namespace Prokl\WordpressCustomTableEditorBundle\Commands;

use Exception;
use LogicException;
use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;
use Prokl\WordpressCustomTableEditorBundle\Services\Utils\FixtureGenerator;
use Prokl\WordpressCustomTableEditorBundle\Services\Utils\SeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class SeedDatabaseCommand
 * @package Local\Bundles\WpMigrationBundle\Command
 *
 * @since 08.04.2021
 */
class SeedDatabaseCommand extends Command
{
    private const DEFAULT_QUANTITY_RECORD = 5;

    /**
     * @var ServiceLocator $entityLocator Локатор с сущностями таблиц.
     */
    private $entityLocator;

    /**
     * @var FixtureGenerator $fixtureGenerator
     */
    private $fixtureGenerator;

    /**
     * @var SeedDatabase $seederDatabase
     */
    private $seederDatabase;

    /**
     * SeedDatabaseCommand constructor.
     *
     * @param FixtureGenerator $fixtureGenerator
     * @param SeedDatabase     $seederDatabase
     * @param ServiceLocator   $entityLocator
     */
    public function __construct(
        FixtureGenerator $fixtureGenerator,
        SeedDatabase $seederDatabase,
        ServiceLocator $entityLocator
    ) {
        $this->entityLocator = $entityLocator;
        $this->fixtureGenerator = $fixtureGenerator;
        $this->seederDatabase = $seederDatabase;

        parent::__construct();
    }

    /**
     * Configure.
     */
    protected function configure() : void
    {
        $this->setName('migrator:seed')
            ->setDescription('Seed table of database from fixture.')
            ->addArgument('table', InputArgument::REQUIRED, 'Table of database')
            ->addArgument('count', InputArgument::OPTIONAL, 'Count of records', static::DEFAULT_QUANTITY_RECORD)
            ->addArgument('truncate', InputArgument::OPTIONAL, 'Truncate data of table', true);
    }

    /**
     * Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $count = (int)$input->getArgument('count');
        $truncate = (bool)$input->getArgument('truncate');

        $table = $input->getArgument('table');

        $output->writeln('Looking for entity of table ' . $table);
        $entity = $this->locateEntityData($table);

        $fixture = $this->fixtureGenerator->fromSchema(
            $entity,
            $count
        );

        $output->writeln('Starting seeding database.');

        $this->seederDatabase->setPrefix('');
        $this->seederDatabase->setTable($table);

        if ($truncate) {
            $this->seederDatabase->truncate();
        }

        $this->seederDatabase->fromFixture($fixture);

        $output->writeln('Seeding table ' . $table .  ' of database completed.');

        return 1;
    }

    /**
     * @param string $table Таблица.
     *
     * @return DataManagerInterface
     * @throws LogicException
     */
    private function locateEntityData(string $table) : DataManagerInterface
    {
        foreach ($this->entityLocator->getProvidedServices() as $serviceId => $value) {
            $service = $this->entityLocator->get($serviceId);
            if ($service->getTableName() === $table) {
                return $service;
            }
        }

        throw new LogicException(
           'Not found entity for table ' . $table
        );
    }
}
