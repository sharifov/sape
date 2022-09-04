<?php
namespace App\Command;

use Symfony\Component\Console\{
	Command\Command,
	Input\InputInterface,
	Input\InputArgument,
	Output\OutputInterface
};
use App\SapeRequest;

class SapeCommand extends Command
{
    private SapeRequest $sape;
    protected static $defaultName = 'sape';

    protected function configure(): void
    {
        $this->addArgument('ticket', InputArgument::REQUIRED, 'AUTH_TICKET token for User cookie');
    }
	
	private function getProjects(): array
	{
		$projects = [];
		if (!empty($this->sape->data['projectsInGroupList'])) {
			foreach ($this->sape->data['projectsInGroupList'] as $project) {
				$projects[$project['domain']] = [
					'id' => $project['id'],
					'name' => $project['name'],
					'date' => date('Y-m-d', strtotime($project['updatedAt']))
				];
			}
		}
		return $projects;
	}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
		$this->sape = SapeRequest::init($input->getArgument('ticket'));
		$this->sape->auth('setToken');
		$this->sape->rest_Projects();
		$projects = $this->getProjects();
		
		var_dump($projects);
		
		return Command::SUCCESS;
    }
}