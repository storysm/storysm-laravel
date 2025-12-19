import { ReaderStore } from "../../stores/reader";

const Alpine = window.Alpine;

Alpine.data("readerControls", () => ({
    showToolbar: true,
    lastScrollY: 0,
    ticking: false, // For requestAnimationFrame optimization

    getScrollbarWidth() {
        const outer = document.createElement("div");
        outer.style.visibility = "hidden";
        outer.style.overflow = "scroll";
        document.body.appendChild(outer);

        const inner = document.createElement("div");
        outer.appendChild(inner);

        const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
        outer.remove();

        return scrollbarWidth;
    },

    init() {
        this.$watch("$store.reader.fullscreen", (value: boolean) => {
            if (value) {
                const scrollbarWidth = this.getScrollbarWidth();
                document.body.style.paddingRight = `${scrollbarWidth}px`;
                document.body.classList.add("overflow-hidden");
                this.lastScrollY = 0;
                this.showToolbar = true;
            } else {
                document.body.classList.remove("overflow-hidden");
                document.body.style.paddingRight = "";
            }
        });
    },

    /**
     * Optimized scroll handler
     * Uses requestAnimationFrame to prevent layout thrashing
     */
    handleScroll(e: Event) {
        if (this.ticking) return;

        this.ticking = true;
        window.requestAnimationFrame(() => {
            const readerStore = this.$store.reader as ReaderStore;
            const isFullscreen = readerStore.fullscreen;

            // Determine scroll source
            const target = isFullscreen
                ? (e.target as HTMLElement)
                : document.scrollingElement || document.documentElement;

            const currentScroll = target.scrollTop;

            // Logic: Show if scrolling up or near top, hide if scrolling down
            if (currentScroll < this.lastScrollY || currentScroll < 100) {
                this.showToolbar = true;
            } else if (
                currentScroll > this.lastScrollY &&
                currentScroll > 100
            ) {
                this.showToolbar = false;
            }

            this.lastScrollY = Math.max(0, currentScroll);
            this.ticking = false;
        });
    },
}));
