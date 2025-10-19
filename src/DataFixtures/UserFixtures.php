<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'email' => 'user1@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'user2@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'admin1@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'admin2@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'user3@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'user4@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'admin3@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'admin4@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'user5@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'user6@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'admin5@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'admin6@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'user7@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'user8@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'admin7@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'admin8@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'user9@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'user10@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
            [
                'email' => 'admin9@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => true,
            ],
            [
                'email' => 'admin10@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'Password123!',
                'subscriptionToNewsletter' => false,
            ],
        ];

        foreach ($usersData as $attributes)
        {
            $user = new User();
            $user->setEmail($attributes['email']);
            $user->setRoles($attributes['roles']);
            $user->setPassword($attributes['password']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, $attributes['password']);
            $user->setPassword($hashedPassword);
            $user->setSubscriptionToNewsletter($attributes['subscriptionToNewsletter']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}