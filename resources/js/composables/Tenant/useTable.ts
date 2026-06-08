import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { onBeforeUnmount, ref } from "vue";

export interface TableFilters {
    search?: string;
    sortBy?: string;
    direction?: string;
    perPage?: number;
    page?: number;
    [key: string]: string | number | undefined;
}

export interface UseTableOptions {
    routeName: string;
    initialFilters?: TableFilters;
    defaultSort?: string;
    defaultDirection?: "asc" | "desc";
    defaultPerPage?: number;
}

export function useTable(options: UseTableOptions) {
    const {
        routeName,
        initialFilters = {},
        defaultSort = "created_at",
        defaultDirection = "desc",
        defaultPerPage = 20,
    } = options;

    const search = ref<string>(initialFilters.search ?? "");
    const sortBy = ref<string>(initialFilters.sortBy ?? defaultSort);
    const direction = ref<"asc" | "desc">(
        (initialFilters.direction as "asc" | "desc") ?? defaultDirection,
    );
    const perPage = ref<number>(initialFilters.perPage ?? defaultPerPage);
    const extraFilters = ref<Record<string, string>>(
        Object.fromEntries(
            Object.entries(initialFilters).filter(
                ([k]) =>
                    ![
                        "search",
                        "sortBy",
                        "direction",
                        "perPage",
                        "page",
                    ].includes(k),
            ),
        ) as Record<string, string>,
    );

    let searchDebounce: ReturnType<typeof setTimeout> | null = null;

    function navigate(page?: number): void {
        const params: Record<string, string | number | undefined> = {
            search: search.value || undefined,
            sortBy: sortBy.value,
            direction: direction.value,
            perPage: perPage.value,
            page,
        };

        for (const [key, value] of Object.entries(extraFilters.value)) {
            params[key] = value || undefined;
        }

        router.get(route(routeName), params, {
            preserveState: true,
            replace: true,
        });
    }

    function applyFilters(): void {
        navigate();
    }

    function onSearchInput(): void {
        if (searchDebounce) {
            clearTimeout(searchDebounce);
        }
        searchDebounce = setTimeout(applyFilters, 380);
    }

    function setFilter(key: string, value: string): void {
        extraFilters.value[key] = value;
        applyFilters();
    }

    function clearFilters(): void {
        extraFilters.value = {};
        applyFilters();
    }

    function toggleSort(key: string): void {
        if (sortBy.value === key) {
            direction.value = direction.value === "asc" ? "desc" : "asc";
        } else {
            sortBy.value = key;
            direction.value = "asc";
        }
        applyFilters();
    }

    function goToPage(page: number): void {
        navigate(page);
    }

    function syncFromFilters(filters: TableFilters): void {
        search.value = filters.search ?? "";
        sortBy.value = filters.sortBy ?? defaultSort;
        direction.value =
            (filters.direction as "asc" | "desc") ?? defaultDirection;
        perPage.value = filters.perPage ?? defaultPerPage;

        const extra: Record<string, string> = {};
        for (const [k, v] of Object.entries(filters)) {
            if (
                !["search", "sortBy", "direction", "perPage", "page"].includes(
                    k,
                )
            ) {
                extra[k] = String(v ?? "");
            }
        }
        extraFilters.value = extra;
    }

    onBeforeUnmount(() => {
        if (searchDebounce) {
            clearTimeout(searchDebounce);
        }
    });

    return {
        search,
        sortBy,
        direction,
        perPage,
        extraFilters,
        applyFilters,
        onSearchInput,
        setFilter,
        clearFilters,
        toggleSort,
        goToPage,
        syncFromFilters,
    };
}
