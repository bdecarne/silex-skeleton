<?php
namespace App\Entity;
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 * Date: 10/12/12
 */
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\NoResultException;

class UserRepository extends EntityRepository implements UserProviderInterface {

    /**
     * Loads the user for the given username.
     */
    public function loadUserByUsername($username)
    {
        $q = $this
          ->createQueryBuilder('u')
          ->where('u.username = :username')
          ->setParameter('username', $username)
          ->getQuery()
        ;

        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(sprintf('Impossible de trouver un utilisateur avec l\'identifiant "%s".', $username), null, 0, $e);
        }
        return $user;
    }

    /**
     * Loads the user for the given username.
     */
    public function equals(UserInterface $user)
    {
        return $this->username === $user->getUsername();
    }
    /**
     * Refreshes the user for the account interface.
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }
        return $this->find($user->getId());
    }

    /**
     * Whether this provider supports the given user class
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }
}
