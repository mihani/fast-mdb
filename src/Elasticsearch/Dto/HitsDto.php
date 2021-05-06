<?php

declare(strict_types=1);

namespace App\Elasticsearch\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class HitsDto
{
    public HitsTotalDto $total;

    #[SerializedName('max_score')]
    public ?float $maxScore;

    public array $hits;
}
