<?php

namespace SymfonyLab\RocketGateReport\Request;

use SymfonyLab\RocketGateReport\MerchantInterface;

interface RequestInterface
{
    public function getLink(): string;

    public function getParams(): array;

    public function getMerchant(): MerchantInterface;

    public function handleResponse(string $data): array;
}
