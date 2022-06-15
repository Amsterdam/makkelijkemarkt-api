<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Token;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TokenFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager): void
    {
        /** @var Account $account */
        $account = $this->getReference(AccountFixtures::REFERENCE_USER_ADMIN);

        /** @var Token $token */
        $token = new Token();
        $token->setAccount($account);
        $token->setLifeTime(9880000);
        $token->setClientApp('admin');

        $manager->persist($token);
        $manager->flush();

        $this->createMany(5, 'token', function ($i) {
            /* @var int $accountReference */
            $accountReference = round($i / 10) + 1;

            /** @var Account $account */
            $account = $this->getReference(AccountFixtures::REFERENCE_USER_USER.'_'.$accountReference);

            /** @var Token $token */
            $token = new Token();
            $token->setAccount($account);
            $token->setLifeTime(28800);
            $token->setClientApp('dashboard');

            return $token;
        });
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
