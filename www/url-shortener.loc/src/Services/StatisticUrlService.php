<?php

namespace App\Services;

use Exception;
use App\Entity\Url;
use App\Repository\UrlRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

class StatisticUrlService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Exception
     */
    public function run(Request $request): array
    {
        if (!empty($hash = $request->get('hash'))) {
            return $this->getUrlByHash($hash);
        } elseif (!empty($url = $request->get('url') )) {
            return $this->getUrlAndCreatedAt($url);
        } elseif (!empty($between = $request->get('between') )) {
            return $this->getBetweenUniqueUrlCount($between);
        } elseif (!empty($domain = $request->get('domain'))) {
            return $this->getUniqueUrlCountByDomain($domain);
        } else {

            return ['status' => 'error', 'message' => 'Invalid query parameter'];
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getUrlByHash(string $hash): array
    {
        /** @var UrlRepository $urlRepository */
            $urlRepository = $this->entityManager->getRepository(Url::class);
            $urlEntity = $urlRepository->findOneByHash($hash);

        return [
            'url' => $urlEntity->getUrl(),
            'hash' => $urlEntity->getHash()
        ];
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getUrlAndCreatedAt(string $url ): array
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->entityManager->getRepository(Url::class);
        $urlEntity = $urlRepository->findOneByUrl($url);

        return [
            'url' => $urlEntity->getUrl(),
            'createdAt' => $urlEntity->getCreatedDate()
        ];
    }

    /**
     * @throws Exception
     */
    private function getBetweenUniqueUrlCount(array $between): array
    {
        $startDate = $this->initDataTimeFormat($between['start_date']);
        $endDate = $this->initDataTimeFormat($between['end_date']);

        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->entityManager->getRepository(Url::class);
        $urlEntity = $urlRepository->getBetweenUniqueUrlCount($startDate,$endDate);

        return ['betweenUniqueUrlCount' => $urlEntity];
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    private function getUniqueUrlCountByDomain(string $domain): array
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->entityManager->getRepository(Url::class);
        $urlEntity = $urlRepository->getUniqueUrlCountByDomain($domain);

        return ['domainUniqueUrlCount' => $urlEntity];
    }

    /**
     * @throws Exception
     */
    private function initDataTimeFormat(string $dataTime): \DateTimeImmutable
    {
        return new \DateTimeImmutable($dataTime);
    }
}