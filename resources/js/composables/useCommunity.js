import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useCommunity() {
    const page = usePage();
    const community = computed(() => page.props.community);
    const url = (path = '') =>
        `/${community.value.key}${path ? '/' + path.replace(/^\/+/, '') : ''}`;
    return { community, url };
}
