<?php


namespace SymfonyLab\RocketGateReport;

final class Merchant implements MerchantInterface
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $name;

    public function __construct(string $id, string $password, string $name)
    {
        $this->id = $id;
        $this->password = $password;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
