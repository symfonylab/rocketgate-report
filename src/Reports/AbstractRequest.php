<?php


namespace SymfonyLab\RocketGate\Reports;

use SymfonyLab\RocketGate\MerchantInterface;
use SymfonyLab\RocketGate\Request\RequestInterface;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var MerchantInterface
     */
    protected $merchant;

    /**
     * @var array
     */
    protected $params = [];

    public function getMerchant(): MerchantInterface
    {
        return $this->merchant;
    }

    public function setMerchant(MerchantInterface $merchant): self
    {
        $this->setParam(RequestParams::MERCHANT_ID, $merchant->getId());
        $this->setParam(RequestParams::MERCHANT_PASSWORD, $merchant->getPassword());
        $this->merchant = $merchant;

        return $this;
    }

    final protected function setParam($key, $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function setFromDate(\DateTimeImmutable $fromDate, bool $withHours = false): self
    {
        $this->setParam(RequestParams::FROM_DATE, $fromDate->format('Y-m-d'));
        if ($withHours) {
            $this->setParam(RequestParams::FROM_HOUR, $fromDate->format('H'));
        }
        return $this;
    }

    public function setToDate(\DateTimeImmutable $toDate, bool $withHours = false): self
    {
        $this->setParam(RequestParams::TO_DATE, $toDate->format('Y-m-d'));
        if ($withHours) {
            $this->setParam(RequestParams::TO_HOUR, $toDate->format('H'));
        }
        return $this;
    }

    public function setTimeZone(string $timeZone): self
    {
        $this->setParam(RequestParams::TIMEZONE, $timeZone);
        return $this;
    }

    public function getTimeZone()
    {
        return $this->getParam(RequestParams::TIMEZONE);
    }

    final protected function getParam($key)
    {
        return $this->params[$key];
    }

    public function setReturnFormat(string $returnFormat): self
    {
        $this->setParam(RequestParams::RETURN_FORMAT, $returnFormat);
        return $this;
    }

    public function getReturnFormat()
    {
        return $this->getParam(RequestParams::RETURN_FORMAT);
    }

    public function setDateColumn(string $dateColumn): self
    {
        $this->setParam(RequestParams::DATE_COLUMN, $dateColumn);
        return $this;
    }

    public function whereSiteIds(string ...$siteIds): self
    {
        $this->setParam(RequestParams::MERCHANT_SITE_ID, implode(',', $siteIds));
        return $this;
    }

    final public function getParams(): array
    {
        return $this->params;
    }
}
