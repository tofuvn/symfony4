<?php

namespace App\DataFixtures;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\UserPreferences;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    private const USERS = [
        [
            'username' => 'john_doe',
            'email' => 'john_doe@doe.com',
            'password' => 'john123',
            'fullName' => 'John Doe',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'theho',
            'email' => 'theho@gmail.com',
            'password' => '123qwert',
            'fullName' => 'THe Ho',
            'roles' => [User::ROLE_ADMIN]

        ],
        [
            'username' => 'marry_gold',
            'email' => 'marry_gold@gold.com',
            'password' => 'marry12345',
            'fullName' => 'Marry Gold',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'super_admin',
            'email' => 'super_admin@admin.com',
            'password' => '12345',
            'fullName' => 'SP',
            'roles' => [User::ROLE_ADMIN]
        ],
    ];

    private const POST_TEXT = [
        'Hello, how are you?',
        'It\'s nice sunny weather today',
        'I need to buy some ice cream!',
        'I wanna buy a new car',
        'There\'s a problem with my phone',
        'I need to go to the doctor',
        'What are you up to today?',
        'Did you watch the game yesterday?',
        'How was your day?'
    ];

    private const LANGUAGES = [
        'en',
        'fr'
    ];


    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {
        $this->loadUser($manager);
        $this->loadMicroPost($manager);
    }

    private function loadMicroPost(ObjectManager $manager)
    {
        for ($i = 0; $i < 30; $i++) {
            $microPost = new MicroPost();
            $microPost->setText(self::POST_TEXT[rand(0, count(self::POST_TEXT) - 1)]);
            $date = new DateTime();
            $date->modify('-' . rand(0,10 ) . 'day');
            $microPost->setTime($date);
            $microPost->setUser($this->getReference(self::USERS[rand(0, count(self::USERS) - 1)]['username']));
            $manager->persist($microPost);
        }

        $manager->flush();
    }

    private function loadUser(ObjectManager $manager)
    {
        foreach (self::USERS as $userData) {
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setFullName($userData['fullName']);
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user, $userData['password']
                )
            );
            $user->setEnabled(true);

            $this->addReference($userData['username'], $user);

            $preferences = new UserPreferences();
            $preferences->setLocale(self::LANGUAGES[random_int(0, count(self::LANGUAGES) - 1)]);

            $user->setPreferences($preferences);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
