<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RoomRepository::class)
 */
class Room
{
    /**
     * @Groups({"roomsInit", "messageCalls"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"roomsInit", "messageCalls"})
     * @ORM\Column(type="string", length=255)
     */
    private $lib;

    /**
     * @Groups({"roomsInit"})
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="room", cascade={"persist"})
     */
    private $messages;

    /**
     * @ORM\ManyToOne(targetEntity=RoomsManager::class, inversedBy="rooms")
     */
    private $roomsManager;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLib(): ?string
    {
        return $this->lib;
    }

    public function setLib(string $lib): self
    {
        $this->lib = $lib;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setRoom($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getRoom() === $this) {
                $message->setRoom(null);
            }
        }

        return $this;
    }

    public function getRoomsManager(): ?RoomsManager
    {
        return $this->roomsManager;
    }

    public function setRoomsManager(?RoomsManager $roomsManager): self
    {
        $this->roomsManager = $roomsManager;

        return $this;
    }
}
