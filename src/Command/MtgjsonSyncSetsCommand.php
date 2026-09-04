<?php

namespace App\Command;

use App\Helper\SetSyncer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mtgjson:sync:sets', description: 'Syncs the sets table (code + release date) from Scryfall')]
class MtgjsonSyncSetsCommand extends Command
{
    public function __construct(private readonly SetSyncer $setSyncer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $this->setSyncer->sync();

        $io->success(sprintf('Synced %d sets.', $count));

        return Command::SUCCESS;
    }
}
