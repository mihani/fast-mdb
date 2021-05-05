<?php

declare(strict_types=1);

namespace App\Elasticsearch\Dto;

class ShardsDto
{
    public int $total;

    public int $successful;

    public int $skipped;

    public int $failed;
}
