import { useMediaQuery } from '@vueuse/core'
import {computed} from 'vue';

/**
 * Composable to detect if the device has a fine pointer AND the screen width is 768px or wider.
 *
 * @returns {Ref<boolean>} A reactive ref that is true if both conditions are met, false otherwise.
 */

export function useIsNoFingers() {
    const mediaQueryString = '(any-pointer: fine) and (min-width: 768px)';
    const matches = useMediaQuery(mediaQueryString);
    const isNoFingers = computed(() => matches.value);
    return isNoFingers;
}