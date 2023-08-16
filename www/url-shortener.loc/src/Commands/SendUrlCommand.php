<?php

namespace App\Commands;

use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SendUrlCommand extends Command
{
    private UrlRepository $urlRepository;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBags;

    public function __construct(
        UrlRepository          $urlRepository,
        EntityManagerInterface $entityManager,
        ParameterBagInterface  $parameterBag
    )
    {
        $this->urlRepository = $urlRepository;
        $this->entityManager = $entityManager;
        $this->parameterBags = $parameterBag;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('send:urls')
            ->setDescription('Send URLs to  endpoint.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //Если не удалось подключиться к серверу через $endpoint замените его на IP address nginx контейнера
        // пример 'http://172.24.0.4:80/send-url'

        $endpoint = $this->parameterBags->get('send_url_endpoint');
        $urls = $this->urlRepository->findBy(['sent_url' => false]);

        if (empty($urls)) {
            return Command::FAILURE;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        foreach ($urls as $url) {
            $data = array(
                'url' => $url->getUrl(),
                'created_date' => $url->getCreatedDate()->format('Y-m-d H:i:s')
            );

            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            if (json_decode(curl_exec($ch), true)['status'] !== 200) {
                return Command::FAILURE;
            }

            curl_close($ch);
            $url->sentUrl();
            $this->entityManager->flush();

            return Command::SUCCESS;

        }
    }
}