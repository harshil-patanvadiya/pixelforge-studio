import { usePoll } from '@inertiajs/vue3';
import { watch, type Ref } from 'vue';

const pollIntervalMilliseconds = 3000;

export function usePollWhileUnfinished(hasUnfinished: Ref<boolean>, only: string[]): void {
    const { start, stop } = usePoll(pollIntervalMilliseconds, { only }, { autoStart: false });

    watch(hasUnfinished, (isUnfinished) => (isUnfinished ? start() : stop()), { immediate: true });
}
