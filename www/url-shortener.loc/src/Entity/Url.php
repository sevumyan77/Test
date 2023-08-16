<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UrlRepository::class)
 */
class Url
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     */
    private $domain;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $hash;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $sent_url = false;

    /**
     * @ORM\Column(name="created_date", type="datetime_immutable")
     */
    private $createdDate;

    /**
     * @ORM\Column( type="boolean", options={"default" : 0})
     */
    private $is_expired= false;

    public function __construct()
    {
        $date = new \DateTimeImmutable();
        $this->setCreatedDate($date);
        $this->setHash($date->format('YmdHis'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeImmutable $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
    public function setExpireDate(): self
    {
        $this->is_expired = true;

        return $this;
    }

    public function sentUrl(): self
    {
        $this->sent_url = true;

        return $this;
    }
    public function getExpireDate(): bool
    {
      return $this->is_expired;
    }

    public function getSentUrl (): bool
    {
        return $this->sent_url;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }
}
