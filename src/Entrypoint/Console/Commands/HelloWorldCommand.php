<?php

declare(strict_types=1);

namespace VendingMachine\Entrypoint\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Hello World Command
 *
 * Example console command demonstrating basic Symfony CLI functionality
 */
#[AsCommand(
    name: 'app:hello-world',
    description: 'Say hello to the world or a specific name',
)]
class HelloWorldCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Name to greet', 'World')
            ->setHelp('This command greets the world or a specific name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        $io->title('Hello World Command');
        $io->note('Greeting: ' . $name);
        $io->success("Hello, {$name}!");
        $io->info('Command executed at: ' . date('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }
}
