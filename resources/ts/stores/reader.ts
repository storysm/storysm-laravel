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
    increaseFontSize(): void;
    decreaseFontSize(): void;
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
            try {
                const prefs = JSON.parse(saved);

                // Validate theme
                if (["light", "sepia", "dark"].includes(prefs.theme)) {
                    this.theme = prefs.theme;
                } else {
                    this.theme = DEFAULTS.theme;
                }

                // Validate font
                if (["sans", "serif", "mono"].includes(prefs.font)) {
                    this.font = prefs.font;
                } else {
                    this.font = DEFAULTS.font;
                }

                // Validate fontSize
                if (
                    typeof prefs.fontSize === "number" &&
                    prefs.fontSize >= 50 &&
                    prefs.fontSize <= 200
                ) {
                    this.fontSize = prefs.fontSize;
                } else {
                    this.fontSize = DEFAULTS.fontSize;
                }

                // Validate maxWidth
                if (
                    typeof prefs.maxWidth === "number" &&
                    prefs.maxWidth >= 30 &&
                    prefs.maxWidth <= 100
                ) {
                    this.maxWidth = prefs.maxWidth;
                } else {
                    this.maxWidth = DEFAULTS.maxWidth;
                }
            } catch (e) {
                console.error(
                    "Failed to parse reader preferences from localStorage:",
                    e
                );
                // Fallback to defaults if parsing fails
                this.resetToDefaults();
            }
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

        this.persist();
    },

    increaseFontSize() {
        this.fontSize = Math.min(200, this.fontSize + 10);
    },

    decreaseFontSize() {
        this.fontSize = Math.max(70, this.fontSize - 10);
    },
} as ReaderStore);
