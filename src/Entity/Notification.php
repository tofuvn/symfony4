<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"like" = "LikeNotification"})
 */
abstract class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column (type="boolean")
     */
    private $seen;

    /**
     * Notification constructor.
     */
    public function __construct()
    {
        $this->seen = false;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return false
     */
    public function getSeen(): bool
    {
        return $this->seen;
    }

    /**
     * @param false $seen
     */
    public function setSeen(bool $seen): void
    {
        $this->seen = $seen;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }


}
