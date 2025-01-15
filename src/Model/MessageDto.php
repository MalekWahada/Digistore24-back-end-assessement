<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class MessageDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $text
    ) {
    }
}
