import { AlpineComponent } from "alpinejs";

const Alpine = window.Alpine;

type ReaderTheme = "light" | "sepia" | "dark";
type ReaderFont = "sans" | "serif" | "mono";

interface ReaderState {
    theme: ReaderTheme;
    font: ReaderFont;
    fontSize: number;
    maxWidth: number;
    fullscreen: boolean;
    showToolbar: boolean;
    lastScrollY: number;
    showResetModal: boolean;
    persist: () => void;
    handleScroll: (e: Event) => void;
    toggleFullscreen: () => void;
    resetToDefaults: () => void;
    confirmReset: () => void;
    cancelReset: () => void;
}

const STORAGE_KEY = "story_reader_prefs";

// Default values
const DEFAULTS = {
    theme: "light" as ReaderTheme,
    font: "sans" as ReaderFont,
    fontSize: 100,
    maxWidth: 65,
};

const readerComponentFactory: () => AlpineComponent<ReaderState> = () => ({
    theme: DEFAULTS.theme,
    font: DEFAULTS.font,
    fontSize: DEFAULTS.fontSize,
    maxWidth: DEFAULTS.maxWidth,
    fullscreen: false,
    showToolbar: true,
    lastScrollY: 0,
    showResetModal: false,

    init() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const prefs = JSON.parse(saved);
            this.theme = prefs.theme || DEFAULTS.theme;
            this.font = prefs.font || DEFAULTS.font;
            this.fontSize = prefs.fontSize || DEFAULTS.fontSize;
            this.maxWidth = prefs.maxWidth || DEFAULTS.maxWidth;
        }

        this.$watch("theme", () => this.persist());
        this.$watch("font", () => this.persist());
        this.$watch("fontSize", () => this.persist());
        this.$watch("maxWidth", () => this.persist());

        this.$watch("fullscreen", (val) => {
            if (val) {
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
        this.showResetModal = true;
    },

    confirmReset() {
        this.theme = DEFAULTS.theme;
        this.font = DEFAULTS.font;
        this.fontSize = DEFAULTS.fontSize;
        this.maxWidth = DEFAULTS.maxWidth;
        this.persist();
        this.showResetModal = false;
    },

    cancelReset() {
        this.showResetModal = false;
    },
});

Alpine.data("reader", readerComponentFactory);
