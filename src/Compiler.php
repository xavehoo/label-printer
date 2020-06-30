<?php

/**
 * This file is part of PhpAidc LabelPrinter package.
 *
 * © Appwilio (https://appwilio.com)
 * © JhaoDa (https://github.com/jhaoda)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpAidc\LabelPrinter;

use PhpAidc\LabelPrinter\Contract\Label;
use PhpAidc\LabelPrinter\Contract\Language;

final class Compiler
{
    /** @var Language */
    private $language;

    public static function create(Language $language): self
    {
        return new self($language);
    }

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function compile(Label $label, int $copies = 1): string
    {
        $instructions = [
            $this->language->translateLabel($label),
        ];

        foreach ($label->getCommands(\get_class($this->language)) as $command) {
            if ($this->language->isSupport($command)) {
                $instructions[] = $this->language->translateCommand($command);
            }
        }

        $instructions[] = $this->language->translatePrint($copies);

        $payload = \array_reduce($instructions, static function ($carry, $item) {
            return $item instanceof \Generator
                ? \array_merge($carry, \iterator_to_array($item, false))
                : \array_merge($carry, $item);
        }, []);

        return \implode($payload);
    }
}
