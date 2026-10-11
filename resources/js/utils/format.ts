export const money = (
    value: number | string,
    decimals: number = 2
): string =>
    Number(value || 0).toLocaleString("es-EC", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });