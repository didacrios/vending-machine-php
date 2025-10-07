<?php

declare(strict_types=1);

namespace VendingMachine\Entrypoint\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use VendingMachine\VendingMachine\Application\Customer\InsertCoin\InsertCoinCommand;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;

#[AsCommand(
    name: 'vending-machine:insert-coin',
    description: 'Insert a coin into the vending machine'
)]
final class InsertCoinConsoleCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly VendingMachineRepositoryInterface $vendingMachineRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'value',
            InputArgument::REQUIRED,
            'Coin value (0.05, 0.10, 0.25, 1.00)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $value = (float) $input->getArgument('value');

        try {
            // Dispatch command to handler
            $this->commandBus->dispatch(new InsertCoinCommand($value));

            // Get updated state for display
            $vendingMachine = $this->vendingMachineRepository->load();
            $totalAmount = $vendingMachine->getInsertedAmount();

            $output->writeln(sprintf(
                '<info>Coin inserted successfully! Total amount: €%.2f</info>',
                $totalAmount->toFloat()
            ));

            return Command::SUCCESS;
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>Invalid coin value: %.2f</error>', $value));
            return Command::FAILURE;
        }
    }
}
