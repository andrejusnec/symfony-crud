<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\Table(name="`group`")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\Regex("/^[\w ,.'-]+$/", message="Invalid characters")
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $admin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
    public function __toString(): string
    {
        return $this->title;
    }

    public function getAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }
    public static function usersNotInGroup(array $users, array $userList)
    {
        $leftUsers = [];
        foreach ($users as $user) {
            $userID = $user->getId();
            foreach ($userList as $one) {
                if ($one->getuser()->getId() == $userID) {
                    continue 2;
                }
            }
            $leftUsers[] = $user;
        }
        return $leftUsers;
    }

}
