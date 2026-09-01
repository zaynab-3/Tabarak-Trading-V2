import { onBeforeUnmount, onMounted, readonly, ref } from 'vue';

const headerVisible = ref(true);

export const storefrontHeaderVisible = readonly(headerVisible);

export function useAutoHideStorefrontHeader() {
    let lastScrollY = 0;
    let direction = 0;
    let distanceInDirection = 0;
    let frameId: number | null = null;

    const update = () => {
        const currentScrollY = Math.max(window.scrollY, 0);
        const delta = currentScrollY - lastScrollY;

        if (Math.abs(delta) >= 1) {
            const nextDirection = delta > 0 ? 1 : -1;

            if (nextDirection !== direction) {
                direction = nextDirection;
                distanceInDirection = 0;
            }

            distanceInDirection += Math.abs(delta);

            if (currentScrollY <= 24) {
                headerVisible.value = true;
            } else if (direction > 0 && currentScrollY > 96 && distanceInDirection >= 18) {
                headerVisible.value = false;
            } else if (direction < 0 && distanceInDirection >= 8) {
                headerVisible.value = true;
            }
        }

        lastScrollY = currentScrollY;
        frameId = null;
    };

    const handleScroll = () => {
        if (frameId === null) {
            frameId = window.requestAnimationFrame(update);
        }
    };

    onMounted(() => {
        lastScrollY = Math.max(window.scrollY, 0);
        window.addEventListener('scroll', handleScroll, { passive: true });
    });

    onBeforeUnmount(() => {
        window.removeEventListener('scroll', handleScroll);

        if (frameId !== null) {
            window.cancelAnimationFrame(frameId);
        }

        headerVisible.value = true;
    });

    return { headerVisible: storefrontHeaderVisible };
}
