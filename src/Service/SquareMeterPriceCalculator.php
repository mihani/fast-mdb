<?php

namespace App\Service;

use App\Elasticsearch\Repository\DvfRepository;
use App\Entity\SquareMeterPrice;
use App\Factory\SquareMeterPriceFactory;
use Doctrine\ORM\EntityManagerInterface;

class SquareMeterPriceCalculator
{
    private EntityManagerInterface $em;
    private DvfRepository $dvfRepository;
    private array $dvfYears;

    public function __construct(EntityManagerInterface $em, array $dvfYears, DvfRepository $dvfRepository)
    {
        $this->em = $em;
        $this->dvfRepository = $dvfRepository;
        $this->dvfYears = $dvfYears;
    }

    public function calculate(string $inseeCode, string $postalCode, string $city = null): array
    {
        $evolutionSquareMeterPriceByYears = $this->em->getRepository(SquareMeterPrice::class)->findByInseeCode($inseeCode);

        if ($evolutionSquareMeterPriceByYears) {
            return $evolutionSquareMeterPriceByYears;
        }

        $evolutionSquareMeterPriceByYears = [];
        // Foreach year present in parameters
        foreach ($this->dvfYears as $dvfYear) {
            $squareMeterPrices = [
                'Maison' => ['total_value' => 0, 'count' => 0],
                'Appartement' => ['total_value' => 0, 'count' => 0],
            ];

            $dvfHitsDto = $this->dvfRepository->getDvfByCity((string) $dvfYear, $postalCode, $city);

            if (is_null($dvfHitsDto)) {
                continue;
            }

            // Foreach dvf documents calculate square meter of dvf
            foreach ($dvfHitsDto->hits as $dvfDocument) {
                $current = $dvfDocument['_source'];

                $surface = (float) $current['actual_build_area'];

                if ($surface <= 0) {
                    continue;
                }

                $squareMeterPrices[$current['premises']['type']]['total_value'] += ((float) $current['land_value'] / $surface);
                $squareMeterPrices[$current['premises']['type']]['count']++;
            }

            foreach ($squareMeterPrices as $type => $squareMeterPrice) {
                $evolutionSquareMeterPriceByYears[] = SquareMeterPriceFactory::create(
                    $squareMeterPrice['total_value'] / $squareMeterPrice['count'],
                    $type,
                    $inseeCode,
                    (string) $dvfYear
                );
            }
        }

        foreach ($evolutionSquareMeterPriceByYears as $squareMeterPrice) {
            $this->em->persist($squareMeterPrice);
        }

        $this->em->flush();

        return $evolutionSquareMeterPriceByYears;
    }
}
