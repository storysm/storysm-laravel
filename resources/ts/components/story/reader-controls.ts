import { ReaderStore } from "../../stores/reader";

const Alpine = window.Alpine;

Alpine.data("readerControls", () => ({
    showToolbar: true,
    lastScrollY: 0,

    init() {
        // Handle body scroll locking when fullscreen is toggled in the store
        this.$watch("$store.reader.fullscreen", (value: boolean) => {
            if (value) {
                document.body.classList.add("overflow-hidden");
                this.lastScrollY = 0;
                this.showToolbar = true;
            } else {
                document.body.classList.remove("overflow-hidden");
            }
        });
    },

    handleScroll(e: Event) {
        const readerStore = this.$store.reader as ReaderStore;
        const isFullscreen = readerStore.fullscreen;
        const target = isFullscreen
            ? (e.target as HTMLElement)
            : window.document.documentElement || window.document.body;

        const currentScroll = isFullscreen ? target.scrollTop : window.scrollY;

        if (currentScroll < this.lastScrollY || currentScroll < 100) {
            this.showToolbar = true;
        } else {
            this.showToolbar = false;
        }

        this.lastScrollY = Math.max(0, currentScroll);
    },
}));
