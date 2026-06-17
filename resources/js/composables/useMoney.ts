export function useMoney() {
    function formatPrice(price: string | number | null | undefined): string {
        if (price === null || price === undefined || price === "") {
            return "-";
        }
        const value = typeof price === "number" ? price : parseFloat(price);
        if (Number.isNaN(value)) {
            return "-";
        }
        return new Intl.NumberFormat("pl-PL", {
            style: "currency",
            currency: "PLN",
        }).format(value);
    }

    return { formatPrice };
}
