<?php

declare(strict_types=1);

namespace App\Elasticsearch\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ResponseDto
{
    public int $took;

    #[SerializedName('_scroll_id')]
    public string $scrollId;

    #[SerializedName('timed_out')]
    public bool $timedOut;

    #[SerializedName('_shards')]
    public ShardsDto $shards;

    public HitsDto $hits;
}
