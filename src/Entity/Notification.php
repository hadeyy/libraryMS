<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:07 AM
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_notifications")
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @Assert\NotBlank()
     * @Assert\Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 140,
     *     minMessage="Title must be at least {{ limit }} characters long.",
     *     maxMessage="Title cannot be longer than {{ limit }} characters."
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 350,
     *     minMessage="Title must be at least {{ limit }} characters long.",
     *     maxMessage="Title cannot be longer than {{ limit }} characters."
     * )
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    private $receiver;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="bool",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $isSeen;

    public function __construct(
        string $title,
        string $content,
        User $receiver
    ) {
        $this->id = Uuid::uuid4();
        $this->title = $title;
        $this->content = $content;
        $this->receiver = $receiver;
        $this->isSeen = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getReceiver(): User
    {
        return $this->receiver;
    }

    public function getIsSeen(): bool
    {
        return $this->isSeen;
    }

    public function setIsSeen(bool $isSeen): void
    {
        $this->isSeen = $isSeen;
    }
}
