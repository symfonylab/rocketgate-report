<?php

namespace SymfonyLab\RocketGate\Reports;

class TransactionsRequest extends AbstractRequest
{
    /**
     * Value | Description
     * td   | Transaction Date (Default)
     * cb   | Chargeback Date
     * b    | Batch Date
     */
    const DATE_COLUMN_TD = 'td';
    const DATE_COLUMN_CB = 'cb';
    const DATE_COLUMN_B = 'b';

    const RETURN_FORMAT_JSON = 'JSON';
    const RETURN_FORMAT_CSV = 'CSV';
    const RETURN_FORMAT_XML = 'XML';

    const TTYPE_AUTH_ONLY = '1';
    const TTYPE_TICKET = '2';
    const TTYPE_SALE = '3';
    const TTYPE_CREDIT = '4';
    const TTYPE_VOID = '5';

    const RESPC_SUCCESS = '0';
    const RESPC_BANK_DECLINE = '1';
    const RESPC_RG_SCRUB_DECLINE = '2';
    const RESPC_SYSTEM_ERROR = '3';
    const RESPC_REJECTED = '4';

    const CARD_AMEX = '3';
    const CARD_VISA = '4';
    const CARD_MASTERCARD = '5';

    public function __construct()
    {
        $this->setParam(RequestParams::METHOD, 'lookupTransaction');
        $this->setTimeZone('UTC');
        $this->setDateColumn(self::DATE_COLUMN_TD);
        $this->setReturnFormat(self::RETURN_FORMAT_JSON);
    }

    public function getLink(): string
    {
        return 'Transactions.cfc';
    }

    public function whereCardType(string $cardType): TransactionsRequest
    {
        $this->setParam(RequestParams::CARD_TYPE_ID, $cardType);

        return $this;
    }

    public function whereTransactionTypeIds(string ...$ttypeIds): TransactionsRequest
    {
        $this->setParam(RequestParams::TRANSACTION_TYPE_ID, implode(',', $ttypeIds));
        return $this;
    }

    public function whereResponseType(string $respcId): TransactionsRequest
    {
        $this->setParam(RequestParams::RESPONSE_TYPE_ID, $respcId);
        return $this;
    }

    public function handleResponse(string $data): array
    {
        if ($this->getReturnFormat() === TransactionsRequest::RETURN_FORMAT_XML) {
            $data = json_decode(json_encode(simplexml_load_string($data)), true);

            return array_map(function ($row) {
                $dateNormalizer = function ($value) {
                    $dateFormat = 'Y/m/d H:i:s';
                    $value = date_create_immutable_from_format($dateFormat, $value, new \DateTimeZone($this->getTimeZone()));
                    return $value ? $value : null;
                };
                $row['tr_date'] = $dateNormalizer($row['tr_date']);
                $row['tr_chargebackdate'] = $dateNormalizer($row['tr_chargebackdate']);
                $row['bat_date'] = $dateNormalizer($row['bat_date']);
                foreach ($row as $k => $v) {
                    if (is_array($row[$k]) && count($row[$k]) === 0) {
                        $row[$k] = null;
                    }
                }
                $row['bankbin'] = (int)($row['bankbin']);
                $row['site_id'] = (int)($row['site_id']);
                $row['merch_id'] = (int)($row['merch_id']);
                $row['expiremonth'] = (int)($row['expiremonth']);
                $row['expireyear'] = (int)($row['expireyear']);
                $row['respc_id'] = (int)($row['respc_id']);
                $row['resp_id'] = (int)($row['resp_id']);
                $row['tr_pay_num_l4'] = (int)($row['tr_pay_num_l4']);
                $row['merch_id_referrer'] = isset($row['merch_id_referrer']) ? (int)($row['merch_id_referrer']) : null;

                return $row;
            }, $data);
        }
        if ($this->getReturnFormat() === TransactionsRequest::RETURN_FORMAT_JSON) {
            $jsonCheck = substr($data, 0, 2);
            if ($jsonCheck === '//') {
                $json = json_decode(substr($data, 2), true);
                $keys = array_map('strtolower', $json['COLUMNS']);
                return array_map(function ($values) use ($keys) {
                    $row = array_combine($keys, $values);

                    $dateNormalizer = function ($value) {
                        $dateFormat = 'F, d Y H:i:s';
                        $value = date_create_immutable_from_format($dateFormat, $value, new \DateTimeZone($this->getTimeZone()));
                        return $value ? $value : null;
                    };

                    $row['tr_date'] = $dateNormalizer($row['tr_date']);
                    $row['tr_chargebackdate'] = $dateNormalizer($row['tr_chargebackdate']);
                    $row['bat_date'] = $dateNormalizer($row['bat_date']);
                    $row['bankbin'] = (int)($row['bankbin']);
                    $row['site_id'] = (int)($row['site_id']);
                    $row['merch_id'] = (int)($row['merch_id']);
                    $row['expiremonth'] = (int)($row['expiremonth']);
                    $row['expireyear'] = (int)($row['expireyear']);
                    $row['merch_id'] = (int)($row['merch_id']);
                    $row['respc_id'] = (int)($row['respc_id']);
                    $row['resp_id'] = (int)($row['resp_id']);
                    $row['tr_pay_num_l4'] = (int)($row['tr_pay_num_l4']);
                    $row['merch_id_referrer'] = isset($row['merch_id_referrer']) ? (int)($row['merch_id_referrer']) : null;

                    return $row;
                }, $json['DATA']);
            }
        }
        return [];
    }
}
