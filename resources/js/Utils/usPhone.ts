const MAX_NATIONAL_DIGITS = 10;

export const usPhoneNationalDigits = (value: string): string => {
    let digits = value.replace(/\D/g, '');

    if (digits.startsWith('1')) digits = digits.slice(1);

    return digits.slice(0, MAX_NATIONAL_DIGITS);
};

export const formatUsPhoneNumber = (value: string): string => {
    const digits = usPhoneNationalDigits(value);
    const areaCode = digits.slice(0, 3);
    const exchange = digits.slice(3, 6);
    const subscriber = digits.slice(6, 10);

    if (!areaCode) return '+1 ';

    let formatted = `+1 (${areaCode}`;
    if (areaCode.length === 3) formatted += ')';
    if (exchange) formatted += ` ${exchange}`;
    if (subscriber) formatted += ` ${subscriber}`;

    return formatted;
};

export const isCompleteUsPhoneNumber = (value: string): boolean =>
    usPhoneNationalDigits(value).length === MAX_NATIONAL_DIGITS;

export const isValidUsPhoneNumber = (value: string): boolean =>
    /^[2-9]\d{2}[2-9]\d{6}$/.test(usPhoneNationalDigits(value));

export const canonicalUsPhoneNumber = (value: string): string =>
    `+1${usPhoneNationalDigits(value)}`;

export const remainingUsPhoneDigits = (value: string): number =>
    Math.max(0, MAX_NATIONAL_DIGITS - usPhoneNationalDigits(value).length);
