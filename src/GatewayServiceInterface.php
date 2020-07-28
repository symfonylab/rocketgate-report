<?php


namespace SymfonyLab\RocketGate;

use SymfonyLab\RocketGate\Request\RequestInterface;

interface GatewayServiceInterface
{
    public function request(RequestInterface $request);
}
