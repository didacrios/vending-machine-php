<?php

declare(strict_types=1);

namespace VendingMachine\Entrypoint\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use VendingMachine\VendingMachine\Application\Customer\Purchase\PurchaseProductCommand;
use VendingMachine\VendingMachine\Application\Customer\Purchase\PurchaseProductResult;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientChangeException;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;

#[AsCommand(
    name: 'vending-machine:purchase-product',
    description: 'Purchase a product from the vending machine'
)]
final class PurchaseProductConsoleCommand extends Command
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
            $command = new PurchaseProductCommand($productName);
            $envelope = $this->commandBus->dispatch($command);

            /** @var PurchaseProductResult $result */
            $result = $envelope->last(HandledStamp::class)?->getResult();

            $output->writeln(sprintf(
                '<info>Product dispensed: %s</info>',
                $productName
            ));

            if (!empty($result->changeCoins)) {
                $changeAmounts = array_map(fn($cents) => sprintf('%.2f', $cents / 100), $result->changeCoins);
                $output->writeln(sprintf(
                    '<info>Change returned: %s</info>',
                    implode(', ', $changeAmounts)
                ));
            }

            return Command::SUCCESS;
        } catch (InsufficientChangeException|InsufficientFundsException|ProductOutOfStockException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Command::FAILURE;
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>Invalid product: %s</error>', $productName));
            return Command::FAILURE;
        }
    }
}
