<?php

namespace App\Command;

use App\Service\ChampionService;
use App\Service\ItemService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UpdateChampAndItem extends Command
{

    protected static string $commandName = 'app:update-database';
    private ItemService $itemService;
    private ChampionService $championService;
    private MailerInterface $mailerInterface;

    public function __construct(ItemService $itemService, ChampionService $championService, MailerInterface $mailer)
    {
        $this->itemService = $itemService;
        $this->championService = $championService;
        $this->mailerInterface = $mailer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::$commandName)
            ->setDescription('Update champions and Items in database');;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {

            $champResp  = $this->championService->setChampInDatabase();
            $itemResp   = $this->itemService->setItemToDatabase();

            $output->writeln([
                '============',
                'ChampUpdate',
                '============',
                '<fg=green>' . $champResp . '</>',
                '============',
                'ItemUpdate',
                '============',
                '<fg=green>' . $itemResp . '</>',
            ]);

            $email = (new Email())
                ->from('updateWithCron@F33ders.com')
                ->to("f33ders@gmail.com")
                ->subject("La tâche cron a bien été executée")
                ->text("la tâche cron c'est bien executée celle-ci a renvoyé le message suivant : Champion : <br>" . $champResp . "<br> Item : ". $itemResp);

            $this->mailerInterface->send($email);

            return Command::SUCCESS;

        } catch (ClientExceptionInterface | DecodingExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            return Command::FAILURE;
        }
    }


}