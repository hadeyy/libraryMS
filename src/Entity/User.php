<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:22 AM
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
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
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registeredAt;

    /**
     * @ORM\Column(type="string")
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BookReservation", mappedBy="reader", cascade={"persist", "remove"})
     */
    private $bookReservations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="receiver")
     */
    private $notifications;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="user", cascade={"persist", "remove"})
     */
    private $activities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author", cascade={"persist", "remove"})
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Book")
     * @ORM\JoinTable(name="users_books",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="book_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $favorites;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="json_array")
     */
    private $roles;

    public function __construct()
    {
        $this->registeredAt = new \DateTime();
        $this->notifications = new ArrayCollection();
        $this->bookReservations = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
        $this->favorites = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username): void
    {
        $this->username = $username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getRegisteredAt()
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt($registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    public function getBookReservations()
    {
        return $this->bookReservations;
    }

    public function addBookReservation($bookReservation): void
    {
        $this->bookReservations->add($bookReservation);
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function addNotification($notification): void
    {
        $this->notifications->add($notification);
    }

    public function getActivities()
    {
        return $this->activities;
    }

    public function addActivity($activity): void
    {
        $this->activities->add($activity);
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addComment($comment): void
    {
        $this->comments->add($comment);
    }

    public function getFavorites()
    {
        return $this->favorites;
    }

    public function addFavorite($book): void
    {
        $this->favorites->add($book);
    }

    public function removeFavorite($book): void
    {
        $this->favorites->removeElement($book);
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function addRole($role): void
    {
        $this->roles[] = $role;
    }

    public function resetRoles()
    {
        $this->roles = ['ROLE_USER'];
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized);
    }

    public function __toString()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
