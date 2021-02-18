<?php


namespace App\Event;


use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserRegisterEvent extends Event
{
    const NAME = 'user.register';
    /**
     * @var User
     */
    private $registerUser;

    /**
     * UserRegisterEvent constructor.
     * @param User $registerUser
     */
    public function __construct(User $registerUser)
    {
        $this->registerUser = $registerUser;
    }

    /**
     * @return User
     */
    public function getRegisterUser(): User
    {
        return $this->registerUser;
    }



}