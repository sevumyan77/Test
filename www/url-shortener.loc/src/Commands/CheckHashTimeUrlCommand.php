<?php

namespace App\Commands;

use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

class CheckHashTimeUrlCommand extends Command
{
    private const URL_HASH_ACTIVE_TIME = 3600;

    private UrlRepository $urlRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UrlRepository $urlRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->urlRepository = $urlRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('check:hash-url')
            ->setDescription('Check hash time url');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $urls = $this->urlRepository->findBy(['is_expired' => false]);

        if (empty($urls)) {
            return Command::FAILURE;
        }

        $currentTime = new \DateTime();

        foreach ($urls as $url) {
            $createdDate = $url->getCreatedDate();
            $timeDifference = $createdDate->getTimestamp() + self::URL_HASH_ACTIVE_TIME - $currentTime->getTimestamp();

            if ($timeDifference < 1) {
                $url->setExpireDate();
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
