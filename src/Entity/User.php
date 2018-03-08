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
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="User with this email already exists.")
 * @UniqueEntity(fields="username", message="Username already taken.")
 */
class User implements UserInterface, \Serializable
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
     *      min = 2,
     *      max = 50,
     *      minMessage = "First name must be at least {{ limit }} characters long.",
     *      maxMessage = "First name cannot be longer than {{ limit }} characters."
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Last name must be at least {{ limit }} characters long",
     *      maxMessage = "Last name cannot be longer than {{ limit }} characters"
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Username must be at least {{ limit }} characters long",
     *      maxMessage = "Username cannot be longer than {{ limit }} characters"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $registeredAt;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Image(
     *     minWidth = 50,
     *     maxWidth = 5000,
     *     minHeight = 50,
     *     maxHeight = 5000,
     *     minWidthMessage="Minimum width expected is {{ min_width }}px.",
     *     maxWidthMessage="Allowed maximum width is {{ max_width }}px.",
     *     minHeightMessage="Minimum height expected is {{ min_height }}px.",
     *     maxHeightMessage="Allowed maximum height is {{ max_height }}px."
     * )
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BookReservation", mappedBy="reader", cascade={"persist", "remove"})
     */
    private $bookReservations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="receiver", cascade={"persist", "remove"})
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Book", cascade={"persist"})
     * @ORM\JoinTable(name="users_books",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="book_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $favorites;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rating", mappedBy="user", cascade={"persist", "remove"})
     */
    private $ratings;

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
     * @Assert\NotBlank()
     */
    private $roles;

    public function __construct(
        string $firstName,
        string $lastName,
        string $username,
        string $email,
        $photo,
        string $plainPassword,
        array $roles = ['ROLE_USER']
    ) {
        $this->id = Uuid::uuid4();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
        $this->email = $email;
        $this->photo = $photo;
        $this->plainPassword = $plainPassword;
        $this->roles = $roles;
        $this->registeredAt = new \DateTime();
        $this->notifications = new ArrayCollection();
        $this->bookReservations = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getRegisteredAt(): \DateTime
    {
        return $this->registeredAt;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    public function getBookReservations(): ArrayCollection
    {
        return $this->bookReservations;
    }

    public function addBookReservation(BookReservation $bookReservation): void
    {
        $this->bookReservations->add($bookReservation);
    }

    public function getNotifications(): ArrayCollection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): void
    {
        $this->notifications->add($notification);
    }

    public function getActivities(): ArrayCollection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): void
    {
        $this->activities->add($activity);
    }

    public function getComments(): ArrayCollection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): void
    {
        $this->comments->add($comment);
    }

    public function getFavorites(): ArrayCollection
    {
        return $this->favorites;
    }

    public function addFavorite(Book $book): void
    {
        $this->favorites->add($book);
    }

    public function removeFavorite(Book $book): void
    {
        $this->favorites->removeElement($book);
    }

    public function getRatings(): ArrayCollection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): void
    {
        $this->ratings->add($rating);
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

    public function addRole(string $role): void
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
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
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

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
