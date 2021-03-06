<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Validator as UserAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *  @UniqueEntity(
 *    fields={"email"},
 *    errorPath="email",
 *    message="This email is already taken."
 * )
 */
class User implements UserInterface, \Serializable

{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters"
     * )
     * @UserAssert\ContainsAlphabet
     *
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 3,
     *      max = 200,
     *      minMessage = "Your password must be at least {{ limit }} characters long",
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Regex("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/")
     * @Assert\NotBlank
     *
     */
    private $email;
    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    public function __construct()
    {
        $this->roles[] = 'ROLE_USER';
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
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    public function __toString(): string
    {
        return $this->name;
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
/******************************************************AUTH******************************************************* */
    public function getUsername()
    {
        return $this->name;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->name,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->name,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized, array('allowed_classes' => false));
    }

    /**********************************************PASSWORD**************************************************/
    public function encodePassword(UserInterface $user, string $plainPassword)
    {

    }
    /**
     * @return bool true if the password is valid, false otherwise
     */
    public function isPasswordValid(UserInterface $user, string $raw)
    {

    }

    /**
     * Checks if an encoded password would benefit from rehashing.
     */
    public function needsRehash(UserInterface $user): bool
    {

    }

    public static function notUserGroups(array $groups, array $groupList)
    {
        $leftGroups = [];
        foreach ($groups as $group) {
            $groupID = $group->getId();
            foreach ($groupList as $one) {
                if ($one->getGrupe()->getId() == $groupID) {
                    continue 2;
                }
            }
            $leftGroups[] = $group;
        }
        return $leftGroups;
    }

    public static function adminGroupCheck(array $relationship, UserGroup $userGroup)
    {
        if (count($relationship) > 1 && isset($relationship)) {
            $count = 0;
            foreach ($relationship as $rel) {
                if ($rel->getGrupe()->getAdmin()) {
                    $count++;
                }
            }
            if ($count <= 1) {
                $useris = $userGroup->getUser();
                $roles = $useris->getRoles();
                if (($key = array_search('ROLE_ADMIN', $roles)) !== false) {
                    unset($roles[$key]);
                }
                $useris->setRoles($roles);
            }
        } else {
            $useris = $userGroup->getUser();
            $roles = $useris->getRoles();
            if (($key = array_search('ROLE_ADMIN', $roles)) !== false) {
                unset($roles[$key]);
            }
            $useris->setRoles($roles);
        }
    }
}
