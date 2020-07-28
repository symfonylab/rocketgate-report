<?php


namespace SymfonyLab\RocketGateReport;

use SymfonyLab\RocketGateReport\Request\RequestInterface;

interface GatewayServiceInterface
{
    public function request(RequestInterface $request);
}
