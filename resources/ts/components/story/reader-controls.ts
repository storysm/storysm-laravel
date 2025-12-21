import { ReaderStore } from "../../stores/reader";

const Alpine = window.Alpine;

// Cache the scrollbar width globally to avoid repeated DOM manipulation
let cachedScrollbarWidth: number | null = null;

function getScrollbarWidth() {
    if (cachedScrollbarWidth !== null) return cachedScrollbarWidth;

    const outer = document.createElement("div");
    outer.style.visibility = "hidden";
    outer.style.overflow = "scroll";
    document.body.appendChild(outer);

    const inner = document.createElement("div");
    outer.appendChild(inner);

    cachedScrollbarWidth = outer.offsetWidth - inner.offsetWidth;
    outer.remove();

    return cachedScrollbarWidth;
}

Alpine.data("readerControls", () => ({
    showToolbar: true,
    lastScrollY: 0,
    ticking: false,

    // Store reference to the handler for cleanup
    _popstateHandler: null as ((event: PopStateEvent) => void) | null,

    init() {
        // Define and store the handler
        this._popstateHandler = (event: PopStateEvent) => {
            const readerStore = this.$store.reader as ReaderStore;
            // If we are in fullscreen and back is pressed, exit fullscreen
            if (readerStore.fullscreen) {
                readerStore.fullscreen = false;
            }
        };

        window.addEventListener("popstate", this._popstateHandler);

        this.$watch("$store.reader.fullscreen", (value: boolean) => {
            if (value) {
                const scrollbarWidth = getScrollbarWidth();
                document.body.style.paddingRight = `${scrollbarWidth}px`;
                document.body.classList.add("overflow-hidden");
                this.lastScrollY = 0;
                this.showToolbar = true;

                // Push state only if not already there (prevents duplicates)
                if (window.history.state?.readerFullscreen !== true) {
                    window.history.pushState({ readerFullscreen: true }, "");
                }
            } else {
                document.body.classList.remove("overflow-hidden");
                document.body.style.paddingRight = "";

                // If the user exited via UI (not back button), sync the history
                // This prevents the user from having to press "Back" twice to leave the page
                if (window.history.state?.readerFullscreen === true) {
                    window.history.back();
                }
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
                : (document.scrollingElement as HTMLElement) ||
                  document.documentElement;

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
