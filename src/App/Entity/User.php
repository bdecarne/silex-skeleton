<?php
namespace App\Entity;
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 * Date: 10/12/12
 */

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Entity(repositoryClass="UserRepository")
 * @Table(name="user")
 */
class User implements UserInterface, \Serializable {
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /** @Column(type="string", length=32, unique=true) */
    private $username;

    /** @Column(type="string", length=32) */
    private $password;

    /**
     * Returns the password used to authenticate the user.
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return mixed
     */
    public function setPassword($password)
    {
        return $this->password = $password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     * @return string The salt
     */
    public function getSalt()
    {
        return null;
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
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $logs
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;
    }

    /**
     * @return mixed
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Removes sensitive data from the user.
     * @return void
     */
    public function eraseCredentials()
    {

    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
          $this->id,
          ) = unserialize($serialized);
    }
}
