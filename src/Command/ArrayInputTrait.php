<?php

declare(strict_types=1);

/*
 * (c) Niels Verbeek <niels@kreable.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nlzet\DoctrineMappingTypingsBundle\Command;

trait ArrayInputTrait
{
    /**
     * @return array<string, string>
     */
    protected function inputToArray(mixed $input): array
    {
        if ('' === $input || (!\is_string($input) && !\is_array($input))) {
            return [];
        }

        $explode = \is_array($input) ? $input : explode(',', $input);
        $input = array_map('trim', $explode);

        $replaced = [];
        foreach ($input as $index => $value) {
            if (!str_contains($value, '=')) {
                $replaced[(string) $index] = $value;
            } else {
                $explode = explode('=', $value);
                $replaced[$explode[0]] = implode('=', \array_slice($explode, 1));
            }
        }

        return $replaced;
    }
}
