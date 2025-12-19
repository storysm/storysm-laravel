const Alpine = window.Alpine;

export type ReaderTheme = "light" | "sepia" | "dark";
export type ReaderFont = "sans" | "serif" | "mono";

const STORAGE_KEY = "reader_prefs";

const DEFAULTS = {
    theme: "dark" as ReaderTheme,
    font: "sans" as ReaderFont,
    fontSize: 100,
    maxWidth: 65,
};

export interface ReaderStore {
    theme: ReaderTheme;
    font: ReaderFont;
    fontSize: number;
    maxWidth: number;
    fullscreen: boolean;

    persist(): void;
    toggleFullscreen(): void;
    resetToDefaults(): void;
}

Alpine.store("reader", {
    theme: DEFAULTS.theme,
    font: DEFAULTS.font,
    fontSize: DEFAULTS.fontSize,
    maxWidth: DEFAULTS.maxWidth,
    fullscreen: false,

    init() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const prefs = JSON.parse(saved);
            this.theme = prefs.theme || DEFAULTS.theme;
            this.font = prefs.font || DEFAULTS.font;
            this.fontSize = prefs.fontSize || DEFAULTS.fontSize;
            this.maxWidth = prefs.maxWidth || DEFAULTS.maxWidth;
        }

        Alpine.effect(() => {
            this.persist();
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

    toggleFullscreen() {
        this.fullscreen = !this.fullscreen;
    },

    resetToDefaults() {
        this.theme = DEFAULTS.theme;
        this.font = DEFAULTS.font;
        this.fontSize = DEFAULTS.fontSize;
        this.maxWidth = DEFAULTS.maxWidth;
    },
} as ReaderStore);
