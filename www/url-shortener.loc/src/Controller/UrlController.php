<?php

namespace App\Controller;

use Exception;
use App\Entity\Url;
use App\Entity\SentUrl;
use App\Repository\UrlRepository;
use App\Services\StatisticUrlService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    /**
     * @Route("/", name="save_url",methods={"POST"})
     */
    public function save(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'Invalid JSON data'], 400);
        }

        if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            return $this->json(['error' => 'Invalid URL format'], 400);
        }

        try {
            $url = new Url();

            $parsedUrl = parse_url($data['url']);
            $domain = $parsedUrl['host'];
            $url->setDomain($domain);
            $url->setUrl($data['url']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($url);
            $entityManager->flush();
        } catch (Exception $exception ) {
            return $this->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }

        return $this->json([
            'url' => $url->getUrl(),
            'hash' => $url->getHash(),
            'domain' => $url->getDomain()
        ]);
    }

    /**
     * @Route("/encode-url", name="encode_url",methods={"GET"})
     * @throws NonUniqueResultException
     */
    public function encodeUrl(Request $request): JsonResponse
    {
        $url = $request->get('url');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->json(['error' => 'Invalid URL'], 400);
        }

        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $urlEntity = $urlRepository->findOneByUrl($url);

        if ($urlEntity === null) {
            return $this->json(['error' => 'URL not found'], 404);
        }

        if ($urlEntity->getExpireDate()) {
            return $this->json(['error' => 'URL hash expired']);
        }

        return $this->json(['hash' => $urlEntity->getHash()]);
    }

    /**
     * @Route("/decode-url", name="decode_url",methods={"GET"})
     * @throws NonUniqueResultException
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        /** @var UrlRepository $urlRepository */
        $hash = $request->get('hash');
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $hashEntity = $urlRepository->findOneByHash($hash);

        if ($hashEntity === null) {
            return $this->json(['error' => 'URL not found'], 404);
        }

        if ($hashEntity->getExpireDate()) {
            return $this->json(['error' => 'URL hash expired']);
        }

        return $this->json(['url' => $hashEntity->getUrl()]);
    }

    /**
     * @Route("/redirect-url", name="redirect_url", methods={"GET"})
     * @throws NonUniqueResultException
     */
    public function redirectUrl(Request $request): RedirectResponse
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($request->get('hash'));

        if (empty($url)) {
            throw $this->createNotFoundException('Non-existent hash.');
        }

        return $this->redirect($url->getUrl());
    }

    /**
     * @Route("/send-url", name="send_url",methods={"POST"})
     */
    public function saveSendUrl(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return $this->json(['message' => 'Failed save data ']);
        }

        $sentUrl = new SentUrl();

        $sentUrl->setUrl($data['url']);
        $sentUrl->setCreatedDate($data['created_date']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($sentUrl);
        $entityManager->flush();

        return $this->json(['status' => 200]);
    }

    /**
     * @Route("apiv1/get-statistic-url", name="index_url",methods={"GET"})
     * @throws Exception
     */
    public function index(Request $request , StatisticUrlService $statisticUrlService): JsonResponse
    {
        $data = $statisticUrlService->run($request);

        return $this->json($data);
    }
}
