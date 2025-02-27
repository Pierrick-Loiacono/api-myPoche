<?php

namespace App\Normalizer;

use NumberFormatter;
use App\Entity\Abonnements;
use App\Entity\Transactions;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AbonnementsNormalizer implements NormalizerInterface
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
            'prochainPrevelement' => $object->getProchainPrelevement()->format('d/m/Y'), // Formatage ISO
            'frequence' => $object->getFrequence(),
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Abonnements;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Abonnements::class => true,
        ];
    }
}