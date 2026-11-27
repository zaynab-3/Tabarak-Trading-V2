<?php

namespace App\Support;

use App\Models\Setting;

final class CompanyPolicy
{
    /**
     * The default policy shown on the storefront and printed on invoices.
     * Keep the bankruptcy sentence qualified because an invoice cannot override
     * rights or obligations created by applicable bankruptcy law.
     */
    public static function defaultText(): string
    {
        return implode("\n", [
            'No returns on opened items.',
            'Final sale: no returns, credits, swaps, or exchanges are accepted after 30 days from delivery.',
            'Tabarak Trading LLC is not responsible for product expiration or shelf-life issues reported more than 30 days after delivery.',
            'Business closure, dissolution, or a bankruptcy filing does not by itself cancel amounts lawfully due. Bankruptcy-related rights and obligations remain subject to applicable law.',
        ]);
    }

    public static function text(): string
    {
        $value = Setting::valueFor('invoice_policy', self::defaultText());

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : self::defaultText();
    }

    /** @return list<string> */
    public static function lines(): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', self::text()) ?: []),
            static fn (string $line): bool => $line !== '',
        ));
    }

    public static function storefrontSummary(): string
    {
        return 'No returns on opened items. Returns, credits, exchanges, and expiration claims must be made within 30 days of delivery.';
    }
}
