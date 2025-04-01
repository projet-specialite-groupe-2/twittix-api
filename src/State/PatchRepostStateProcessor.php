<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\RepostPatchDTO;
use App\Entity\Repost;
use Doctrine\ORM\EntityManagerInterface;

class PatchRepostStateProcessor implements ProcessorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Repost
    {
        if (!$data instanceof RepostPatchDTO) {
            return null;
        }

        // Find the existing entity
        $repost = $this->entityManager->getRepository(Repost::class)->find($uriVariables['id']);
        if ($repost === null) {
            throw new \RuntimeException('Repost not found');
        }

        // Update only the allowed field (comment)
        if ($data->comment !== null) {
            $repost->setComment($data->comment);
        }

        $this->entityManager->persist($repost);
        $this->entityManager->flush();

        return $repost;
    }
}
