<?php

declare(strict_types=1);

namespace VendingMachine\Entrypoint\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use VendingMachine\VendingMachine\Application\Command\GetProductCommand;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;

#[AsCommand(
    name: 'vending-machine:get-product',
    description: 'Purchase a product from the vending machine'
)]
final class GetProductConsoleCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'product',
            InputArgument::REQUIRED,
            'Product name (WATER, JUICE, SODA)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productName = $input->getArgument('product');

        try {
            // Dispatch command to handler
            $command = new GetProductCommand($productName);
            $this->commandBus->dispatch($command);

            $output->writeln(sprintf(
                '<info>Product dispensed: %s</info>',
                $productName
            ));

            return Command::SUCCESS;
        } catch (InsufficientFundsException|ProductOutOfStockException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Command::FAILURE;
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>Invalid product: %s</error>', $productName));
            return Command::FAILURE;
        }
    }
}
