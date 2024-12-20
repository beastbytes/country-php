<?php
/**
 * @copyright Copyright © 2024 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

use BeastBytes\Country\CountryFactoryInterface;
use BeastBytes\Country\Php\CountryFactory;

return [
    CountryFactoryInterface::class => CountryFactory::class
];
