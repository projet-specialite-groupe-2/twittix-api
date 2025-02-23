<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class PostUserStateProcessor implements ProcessorInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        /**
         * @var User $data
         */
        $password = $data->getPassword();
        if ($password !== null) {
            $data->setPassword($this->passwordHasher->hashPassword($data, $password));
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
