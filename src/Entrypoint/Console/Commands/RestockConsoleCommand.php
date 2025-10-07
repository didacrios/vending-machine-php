<?php

declare(strict_types=1);

namespace VendingMachine\Entrypoint\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use VendingMachine\VendingMachine\Application\Service\GetInventory\GetInventoryQuery;
use VendingMachine\VendingMachine\Application\Service\GetInventory\GetInventoryResult;
use VendingMachine\VendingMachine\Application\Service\Restock\RestockCommand;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

#[AsCommand(
    name: 'vending-machine:restock',
    description: 'Restock the vending machine with products and change'
)]
final class RestockConsoleCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('water', null, InputOption::VALUE_OPTIONAL, 'Water stock quantity')
            ->addOption('juice', null, InputOption::VALUE_OPTIONAL, 'Juice stock quantity')
            ->addOption('soda', null, InputOption::VALUE_OPTIONAL, 'Soda stock quantity')
            ->addOption('coin-5', null, InputOption::VALUE_OPTIONAL, '0.05 coins quantity')
            ->addOption('coin-10', null, InputOption::VALUE_OPTIONAL, '0.10 coins quantity')
            ->addOption('coin-25', null, InputOption::VALUE_OPTIONAL, '0.25 coins quantity')
            ->addOption('coin-100', null, InputOption::VALUE_OPTIONAL, '1.00 coins quantity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $products = [];
        $change = [];

        if ($water = $input->getOption('water')) {
            $products[Product::WATER] = (int) $water;
        }
        if ($juice = $input->getOption('juice')) {
            $products[Product::JUICE] = (int) $juice;
        }
        if ($soda = $input->getOption('soda')) {
            $products[Product::SODA] = (int) $soda;
        }

        if ($coin5 = $input->getOption('coin-5')) {
            $change[5] = (int) $coin5;
        }
        if ($coin10 = $input->getOption('coin-10')) {
            $change[10] = (int) $coin10;
        }
        if ($coin25 = $input->getOption('coin-25')) {
            $change[25] = (int) $coin25;
        }
        if ($coin100 = $input->getOption('coin-100')) {
            $change[100] = (int) $coin100;
        }

        if (empty($products) && empty($change)) {
            $output->writeln('<error>Please specify at least one product or coin to restock</error>');
            return Command::FAILURE;
        }

        try {
            $this->commandBus->dispatch(new RestockCommand($products, $change));

            // Query to get current inventory (CQRS)
            $envelope = $this->queryBus->dispatch(new GetInventoryQuery());
            /** @var GetInventoryResult $inventory */
            $inventory = $envelope->last(HandledStamp::class)?->getResult();

            $output->writeln('<info>Machine restocked successfully!</info>');
            $output->writeln('');
            $output->writeln('<comment>Current inventory:</comment>');

            // Show all products
            foreach ($inventory->products as $productName => $quantity) {
                $output->writeln(sprintf('  %s: %d', $productName, $quantity));
            }

            $output->writeln('');
            $output->writeln('<comment>Current change:</comment>');
            $coinLabels = [5 => '0.05', 10 => '0.10', 25 => '0.25', 100 => '1.00'];
            foreach ($inventory->change as $coinValue => $quantity) {
                $output->writeln(sprintf('  €%s: %d coins', $coinLabels[$coinValue], $quantity));
            }

            return Command::SUCCESS;
        } catch (HandlerFailedException $e) {
            // Symfony Messenger wraps handler exceptions in HandlerFailedException
            // Extract the original exception to show a clean error message
            foreach ($e->getWrappedExceptions() as $wrappedException) {
                $output->writeln(sprintf('<error>%s</error>', $wrappedException->getMessage()));
                return Command::FAILURE;
            }
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Unexpected error: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}

