<?php


namespace SymfonyLab\RocketGate\Reports;

class MembershipsRequest extends AbstractRequest
{
    /**
     * @var string
     * Value | Description
     * J | Join
     * R | Rebill
     * C | Cancel/ Expiration
     * CR |Cancel Request
     * L |Last Updated
     */
    const DATE_COLUMN_J = 'J';
    const DATE_COLUMN_R = 'R';
    const DATE_COLUMN_C = 'C';
    const DATE_COLUMN_CR = 'CR';
    const DATE_COLUMN_L = 'L';

    const RETURN_FORMAT_JSON = 'JSON';
    const RETURN_FORMAT_CSV = 'CSV';
    const RETURN_FORMAT_XML = 'XML';

    const RESPC_REJECTED = '4';

    const CARD_AMEX = '3';
    const CARD_VISA = '4';
    const CARD_MASTERCARD = '5';

    public function __construct()
    {
        $this->setParam(RequestParams::METHOD, 'getData');
        $this->setTimeZone('UTC');
        $this->setDateColumn(self::DATE_COLUMN_J);
        $this->setReturnFormat(self::RETURN_FORMAT_JSON);
    }

    public function getLink(): string
    {
        return 'reports/membership/Export.cfc';
    }

    public function whereCardType(string $cardType): MembershipsRequest
    {
        $this->setParam(RequestParams::CARD_TYPE_ID, $cardType);

        return $this;
    }

    public function whereCustomerId(string $customerId): MembershipsRequest
    {
        $this->setParam(RequestParams::CUSTOMER_ID, $customerId);
        return $this;
    }

    public function handleResponse(string $data): array
    {
        if ($this->getReturnFormat() === MembershipsRequest::RETURN_FORMAT_XML) {
            $data = json_decode(json_encode(simplexml_load_string($data)), true);

            return array_map(function ($row) {
                $dateNormalizer = function ($value) {
                    $dateFormat = 'Y/m/d H:i:s';
                    $value = date_create_immutable_from_format($dateFormat, $value, new \DateTimeZone('UTC'));
                    return $value ? $value : null;
                };
                $row['rebill_start_date'] = $dateNormalizer($row['rebill_start_date']);
                $row['rebill_end_date'] = $dateNormalizer($row['rebill_end_date']);
                $row['rebill_date'] = $dateNormalizer($row['rebill_date']);
                $row['rebill_cancel_request_date'] = $dateNormalizer($row['rebill_cancel_request_date']);
                $row['rebill_last_updated_date'] = $dateNormalizer($row['rebill_last_updated_date']);
                foreach ($row as $k => $v) {
                    if (is_array($row[$k]) && count($row[$k]) === 0) {
                        $row[$k] = null;
                    }
                }
                return $row;
            }, $data);
        }
        if ($this->getReturnFormat() === MembershipsRequest::RETURN_FORMAT_JSON) {
            $jsonCheck = substr($data, 0, 2);

            if ($jsonCheck === '//') {
                $json = json_decode(substr($data, 2), true);
                $keys = array_map('strtolower', $json['COLUMNS']);
                return array_map(function ($values) use ($keys) {
                    $row = array_combine($keys, $values);

                    $dateNormalizer = function ($value) {
                        $dateFormat = 'F, d Y H:i:s';
                        $value = date_create_immutable_from_format($dateFormat, $value, new \DateTimeZone('UTC'));
                        return $value ? $value : null;
                    };

                    $row['rebill_start_date'] = $dateNormalizer($row['rebill_start_date']);
                    $row['rebill_end_date'] = $dateNormalizer($row['rebill_end_date']);
                    $row['rebill_date'] = $dateNormalizer($row['rebill_date']);
                    $row['rebill_cancel_request_date'] = $dateNormalizer($row['rebill_cancel_request_date']);
                    $row['rebill_last_updated_date'] = $dateNormalizer($row['rebill_last_updated_date']);

                    return $row;
                }, $json['DATA']);
            }
        }
        return [];
    }
}
