import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function useFeatures() {
    const page = usePage();

    const features = computed<string[]>(
        () => page.props.auth?.features ?? [],
    );

    const hasFeature = (feature?: string): boolean =>
        !feature || features.value.includes(feature);

    return { features, hasFeature };
}
