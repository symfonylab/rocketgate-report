<?php

namespace SymfonyLab\RocketGate\Request;

use SymfonyLab\RocketGate\MerchantInterface;

interface RequestInterface
{
    public function getLink(): string;

    public function getParams(): array;

    public function getMerchant(): MerchantInterface;

    public function handleResponse(string $data): array;
}
