import { ReaderStore } from "../../stores/reader";

const Alpine = window.Alpine;

Alpine.data("readerControls", () => ({
    showToolbar: true,
    lastScrollY: 0,
    ticking: false,

    // Store reference to the handler for cleanup
    _popstateHandler: null as ((event: PopStateEvent) => void) | null,

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
        // Define and store the handler
        this._popstateHandler = (event: PopStateEvent) => {
            const readerStore = this.$store.reader as ReaderStore;
            if (readerStore.fullscreen) {
                readerStore.fullscreen = false;
            }
        };

        window.addEventListener("popstate", this._popstateHandler);

        this.$watch("$store.reader.fullscreen", (value: boolean) => {
            if (value) {
                const scrollbarWidth = this.getScrollbarWidth();
                document.body.style.paddingRight = `${scrollbarWidth}px`;
                document.body.classList.add("overflow-hidden");
                this.lastScrollY = 0;
                this.showToolbar = true;

                window.history.pushState({ readerFullscreen: true }, "");
            } else {
                document.body.classList.remove("overflow-hidden");
                document.body.style.paddingRight = "";
            }
        });
    },

    /**
     * Alpine.js lifecycle hook for cleanup
     */
    destroy() {
        if (this._popstateHandler) {
            window.removeEventListener("popstate", this._popstateHandler);
        }
    },

    handleScroll(e: Event) {
        if (this.ticking) return;

        this.ticking = true;
        window.requestAnimationFrame(() => {
            const readerStore = this.$store.reader as ReaderStore;
            const isFullscreen = readerStore.fullscreen;

            const target = isFullscreen
                ? (e.target as HTMLElement)
                : document.scrollingElement || document.documentElement;

            const currentScroll = target.scrollTop;

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
