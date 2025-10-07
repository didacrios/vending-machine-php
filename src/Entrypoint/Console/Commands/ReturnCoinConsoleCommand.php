<?php

declare(strict_types=1);

namespace VendingMachine\Entrypoint\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use VendingMachine\VendingMachine\Application\Customer\ReturnCoin\ReturnCoinCommand;
use VendingMachine\VendingMachine\Application\Customer\ReturnCoin\ReturnCoinResult;

#[AsCommand(
    name: 'vending-machine:return-coin',
    description: 'Return all inserted coins from the vending machine'
)]
final class ReturnCoinConsoleCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $command = new ReturnCoinCommand();
            $envelope = $this->commandBus->dispatch($command);

            /** @var ReturnCoinResult $result */
            $result = $envelope->last(HandledStamp::class)?->getResult();

            if (empty($result->returnedCoins)) {
                $output->writeln('<comment>No coins to return</comment>');
                return Command::SUCCESS;
            }

            $output->writeln('<info>Coins returned:</info>');
            foreach ($result->returnedCoins as $coin) {
                $output->writeln(sprintf('  €%.2f', $coin->value()->toFloat()));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error returning coins: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}

