<?php

namespace App\Normalizer;

use App\Entity\Transactions;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use NumberFormatter;

class TransactionsNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        // $data = $this->normalizer->normalize($object, $format, $context);
        $formatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);

        return [
            'id' => $object->getId(),
            'label' => ucfirst($object->getLabel()), // Met la première lettre en majuscule
            'montant' => $formatter->format($object->getMontant()) . ' €', // Format avec séparateurs de milliers
            'formattedDate' => $object->getDate()->format('d/m/Y'), // Formatage ISO
            'type' => $object->getTransactionsTypes()->getCode(),
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Transactions;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Transactions::class => true,
        ];
    }
}