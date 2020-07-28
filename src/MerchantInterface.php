<?php

namespace SymfonyLab\RocketGate;

interface MerchantInterface
{
    public function getId(): string;

    public function getPassword(): string;

    public function getName(): string;
}
