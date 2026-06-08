export function useDate() {
    function formatDate(value: string | null | undefined): string {
        if (!value) {
            return "—";
        }
        return new Date(value).toLocaleDateString("pl-PL", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }

    return { formatDate };
}
