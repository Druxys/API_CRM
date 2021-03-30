<?php

namespace App\Entity;

use App\Repository\AgendaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=AgendaRepository::class)
 */
class Agenda
{
    use TimestampableEntity;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="agendas")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=AgendaLine::class, mappedBy="agenda")
     */
    private $agendaLines;

    public function __construct()
    {
        $this->agendaLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return Collection|AgendaLine[]
     */
    public function getAgendaLines(): Collection
    {
        return $this->agendaLines;
    }

    public function addAgendaLine(AgendaLine $agendaLine): self
    {
        if (!$this->agendaLines->contains($agendaLine)) {
            $this->agendaLines[] = $agendaLine;
            $agendaLine->setAgenda($this);
        }

        return $this;
    }

    public function removeAgendaLine(AgendaLine $agendaLine): self
    {
        if ($this->agendaLines->removeElement($agendaLine)) {
            // set the owning side to null (unless already changed)
            if ($agendaLine->getAgenda() === $this) {
                $agendaLine->setAgenda(null);
            }
        }

        return $this;
    }
}
