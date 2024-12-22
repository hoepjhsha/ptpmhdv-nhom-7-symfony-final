<?php

namespace App\Command;

use App\Services\SpayLaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:apply-late-fee',
    description: 'Apply late fees for overdue installments if unpaid past the 10th of the month.'
)]
class ApplyLateFeeCommand extends Command
{
    protected static $defaultName = 'app:apply-late-fee';

    private SpayLaterService $spayLaterService;

    public function __construct(SpayLaterService $spayLaterService)
    {
        $this->spayLaterService = $spayLaterService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Apply late fees for overdue installments if unpaid past the 10th of the month.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->spayLaterService->applyLateFeeForOverdueInstallment();
        $output->writeln('Late fees have been applied to overdue installments.');
        return Command::SUCCESS;
    }
}
