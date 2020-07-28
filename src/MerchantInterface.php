<?php

namespace SymfonyLab\RocketGateReport;

interface MerchantInterface
{
    public function getId(): string;

    public function getPassword(): string;

    public function getName(): string;
}
