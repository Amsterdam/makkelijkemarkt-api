<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Controller\LoginController;
use App\Entity\Account;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class AccountFixtures extends BaseFixture
{
    public const REFERENCE_USER_ADMIN = 'user_admin';
    public const REFERENCE_USER_SENIOR = 'user_senior';
    public const REFERENCE_USER_USER = 'user_user';

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    protected function loadData(ObjectManager $manager): void
    {
        /** @var string $ut */
        $ut = '1461067200'; // = 2016-04-19T12:00:00+00:00 in ISO 8601

        $account = new Account();
        $account->setNaam(LoginController::READONLY_ACCOUNT_NAME);
        $account->setEmail('test@example.com');
        $account->setRole('ROLE_ADMIN');
        $account->setUsername(LoginController::READONLY_ACCOUNT_NAME);
        $account->setAttempts(0);
        $account->setLastAttempt($this->faker->dateTimeBetween($ut, 'now'));
        $account->setLocked(false);
        $account->setActive(true);
        $account->setPassword($this->userPasswordEncoder->encodePassword(
            $account,
            'insecure'
        ));

        $manager->persist($account);
        $manager->flush();

        $account = new Account();
        $account->setNaam('Salmagundi');
        $account->setEmail('webmaster@example.local');
        $account->setRole('ROLE_ADMIN');
        $account->setUsername('salmagundi');
        $account->setPassword('plain-for-now');
        $account->setAttempts(0);
        $account->setLastAttempt($this->faker->dateTimeBetween($ut, 'now'));
        $account->setLocked(false);
        $account->setActive(true);
        $account->setPassword($this->userPasswordEncoder->encodePassword(
            $account,
            'insecure'
        ));

        $manager->persist($account);
        $this->addReference(self::REFERENCE_USER_ADMIN, $account);
        $manager->flush();

        $this->createMany(10, self::REFERENCE_USER_SENIOR, function ($i) {
            /** @var string $ut */
            $ut = '1461067200'; // = 2016-04-19T12:00:00+00:00 in ISO 8601

            $account = new Account();
            $account->setNaam($this->faker->firstName.' '.$this->faker->lastName);
            $account->setEmail(sprintf('senior%d@example.local', $i));
            $account->setRole('ROLE_SENIOR');
            $account->setUsername($this->faker->unique()->username);
            $account->setPassword('plain-for-now');
            $account->setAttempts(0);
            $account->setLastAttempt($this->faker->dateTimeBetween($ut, 'now'));
            $account->setLocked(false);
            $account->setActive(true);
            $account->setPassword($this->userPasswordEncoder->encodePassword(
                $account,
                'Pa$$w0rd'
            ));

            return $account;
        });

        $this->createMany(100, self::REFERENCE_USER_USER, function ($i) {
            /** @var string $ut */
            $ut = '1461067200'; // = 2016-04-19T12:00:00+00:00 in ISO 8601

            $account = new Account();
            $account->setNaam($this->faker->firstName.' '.$this->faker->lastName);
            $account->setEmail(sprintf('user%d@example.local', $i));
            $account->setRole('ROLE_USER');
            $account->setUsername($this->faker->unique()->username);
            $account->setPassword('plain-for-now');
            $account->setAttempts($this->faker->numberBetween(0, 8));
            $account->setLastAttempt($this->faker->dateTimeBetween($ut, 'now'));
            $account->setLocked($this->faker->boolean());
            $account->setActive($this->faker->boolean());
            $account->setPassword($this->userPasswordEncoder->encodePassword(
                $account,
                'Pa$$w0rd'
            ));

            return $account;
        });
    }
}
