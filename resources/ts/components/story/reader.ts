const Alpine = window.Alpine;

type ReaderTheme = "light" | "sepia" | "dark";
type ReaderFont = "sans" | "serif" | "mono";

interface ReaderStore {
    theme: ReaderTheme;
    font: ReaderFont;
    fontSize: number;
    maxWidth: number;
    fullscreen: boolean;
    showToolbar: boolean;
    lastScrollY: number;

    init: () => void;
    persist: () => void;
    handleScroll: (e: Event) => void;
    toggleFullscreen: () => void;
    resetToDefaults: () => void;
}

const STORAGE_KEY = "story_reader_prefs";

const DEFAULTS = {
    theme: "dark" as ReaderTheme,
    font: "sans" as ReaderFont,
    fontSize: 100,
    maxWidth: 65,
};

Alpine.store("reader", {
    theme: DEFAULTS.theme,
    font: DEFAULTS.font,
    fontSize: DEFAULTS.fontSize,
    maxWidth: DEFAULTS.maxWidth,
    fullscreen: false,
    showToolbar: true,
    lastScrollY: 0,

    init() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const prefs = JSON.parse(saved);
            this.theme = prefs.theme || DEFAULTS.theme;
            this.font = prefs.font || DEFAULTS.font;
            this.fontSize = prefs.fontSize || DEFAULTS.fontSize;
            this.maxWidth = prefs.maxWidth || DEFAULTS.maxWidth;
        }

        // Watchers are not natively available in stores effectively without effects,
        // but we can just call persist() in the setters or use Alpine.effect if needed.
        // For simplicity in a store, we call persist manually or use a simple effect hook if available.
        // Here, we'll hook into Alpine.effect to watch state changes.
        Alpine.effect(() => {
            // Access properties to register dependency
            const _t = this.theme;
            const _f = this.font;
            const _s = this.fontSize;
            const _w = this.maxWidth;
            this.persist();
        });

        Alpine.effect(() => {
            if (this.fullscreen) {
                document.body.classList.add("overflow-hidden");
            } else {
                document.body.classList.remove("overflow-hidden");
            }
        });
    },

    persist() {
        localStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({
                theme: this.theme,
                font: this.font,
                fontSize: this.fontSize,
                maxWidth: this.maxWidth,
            })
        );
    },

    handleScroll(e: Event) {
        const target = this.fullscreen
            ? (e.target as HTMLElement)
            : window.document.documentElement || window.document.body;

        const currentScroll = this.fullscreen
            ? target.scrollTop
            : window.scrollY;

        // Logic: Show toolbar if scrolling up or near top
        if (currentScroll < this.lastScrollY || currentScroll < 100) {
            this.showToolbar = true;
        } else {
            this.showToolbar = false;
        }
        this.lastScrollY = Math.max(0, currentScroll);
    },

    toggleFullscreen() {
        this.fullscreen = !this.fullscreen;
        this.lastScrollY = 0;
        this.showToolbar = true;
    },

    resetToDefaults() {
        this.theme = DEFAULTS.theme;
        this.font = DEFAULTS.font;
        this.fontSize = DEFAULTS.fontSize;
        this.maxWidth = DEFAULTS.maxWidth;
        this.persist();
    },
} as ReaderStore);
