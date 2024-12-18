<?php

declare(strict_types=1);

namespace BeastBytes\Country\\Php;

use BeastBytes\Country\Country;
use BeastBytes\Country\CountryFactoryInterface;
use InvalidArgumentException;

final class CountryFactory implements CountryFactoryInterface
{
    public const string INVALID_DATA_EXCEPTION = '`$data` must be an array of country data, a path to a file that returns an array of country data, or `null` to use local data';

    public function __construct(private array|string|null $data = null)
    {
        if ($this->data === null) {
            $this->data = require __DIR__ . '/data/data.php';
        } elseif (is_string($this->data)) {
            $this->data = require $this->data;
        }

        if (!is_array($this->data)) {
            throw new InvalidArgumentException(self::INVALID_DATA_EXCEPTION);
        }
    }

    /**
     * @param string $search ISO-3166 alpha-2, alpha-3, or numeric country code
     * @return Country|null
     */
    public function create(string $search): ?Country
    {
        $search = strtoupper($search);

        if (PHP_VERSION_ID >= 80400) {
            $result = match (true) {
                (strlen($search) === 2) => array_find($this->data, function($country) use ($search) {
                    return $country['alpha2'] === $search;
                }),
                (is_numeric($search)) => array_find($this->data, function($country) use ($search) {
                    return $country['numeric'] === $search;
                }),
                default => array_find($this->data, function($country) use ($search) {
                    return $country['alpha3'] === $search;
                })
            };
        } else {
            $result = match(true) {
                (strlen($search) === 2) => $this->findByAlpha2($search),
                (is_numeric($search)) => $this->findByNumeric($search),
                default => $this->findByAlpha3($search)
            };
        }

        return $result === null
            ? null
            : new Country(
                array_merge(
                    $result, [
                        'flag' => file_get_contents(__DIR__ . '/data/flags/' . $result['alpha2'] . '.svg')
                    ]
                )
            )
        ;
    }

    // PHP_VERSION_ID < 80400
    private function findByAlpha2(string $search): ?array
    {
        foreach ($this->data as $country) {
            if ($country['alpha2'] === $search) {
                return $country;
            }
        }

        return null;
    }

    private function findByAlpha3(string $search): ?array
    {
        foreach ($this->data as $country) {
            if ($country['alpha3'] === $search) {
                return $country;
            }
        }

        return null;
    }

    private function findByNumeric(string $search): ?array
    {
        foreach ($this->data as $country) {
            if ($country['numeric'] === $search) {
                return $country;
            }
        }

        return null;
    }
}
