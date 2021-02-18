<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields="email", message="This email is already used")
 * @UniqueEntity(fields="username", message="This username is already used")
 */
class User implements UserInterface, UserCheckerInterface, Serializable
{

    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=50)
     */
    private $username;
    /**
     * @ORM\Column(type="string")
     */
    private $password;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=4096)
     */
    private $plainPassword;
    /**
     * @ORM\Column(type="string", length=254, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;
    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(min=4, max=50)
     */
    private $fullName;
    /**
     * @var array
     * @ORM\Column(type="simple_array")
     */
    private $roles;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MicroPost", mappedBy="user" )
     */
    private $posts;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="following")
     */
    private $followers;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="followers")
     * @ORM\JoinTable(name="following",
     *     joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *     @ORM\JoinColumn(name="following_user_id", referencedColumnName="id")
     *  }
     * )
     */
    private $following;
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\MicroPost", mappedBy="likedBy")
     */
    private $postsLiked;


    /**
     * @ORM\Column(type="string", nullable=true, length=30)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserPreferences", cascade={"persist"})
     */
    private $preferences;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->postsLiked = new ArrayCollection();
        $this->roles = [self::ROLE_USER];
        $this->enabled = false;
    }

    /**
     * @return ArrayCollection
     */
    public function getPosts(): ArrayCollection
    {
        return $this->posts;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    public function eraseCredentials()
    {

    }

//    public function unserialize($serialized)
//    {
//        list(
//            $this->id, $this->username, $this->password, $this->enabled
//            ) = unserialize($serialized);
//    }

    public function serialize(): ?string
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->enabled
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->username, $this->password, $this->enabled) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @return Collection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    public function follow(User $user)
    {
        if (!$this->following->contains($user)) {
            $this->following->add($user);
        }
    }

    /**
     * @return Collection
     */
    public function getPostsLiked(): Collection
    {
        return $this->postsLiked;
    }

    /**
     * @return mixed
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param mixed $confirmationToken
     */
    public function setConfirmationToken($confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function checkPreAuth(UserInterface $user)
    {
        // TODO: Implement checkPreAuth() method.
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getEnabled()) {
            throw new CustomUserMessageAccountStatusException('Please confirm account');
        }
    }

    /**
     * @return false
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param false $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function checkPostAuth(UserInterface $user)
    {
        // TODO: Implement checkPostAuth() method.
    }

    /**
     * @return UserPreferences| null
     */
    public function getPreferences(): ?UserPreferences
    {
        return $this->preferences;
    }

    /**
     * @param mixed $preferences
     */
    public function setPreferences($preferences): void
    {
        $this->preferences = $preferences;
    }




}
