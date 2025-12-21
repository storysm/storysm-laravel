const Alpine = window.Alpine;

export type ReaderTheme = "light" | "sepia" | "dark";
export type ReaderFont = "sans" | "serif" | "mono";

const THEMES: ReaderTheme[] = ["light", "sepia", "dark"];
const FONTS: ReaderFont[] = ["sans", "serif", "mono"];

const LIMITS = {
    fontSize: { min: 70, max: 200, step: 10 },
    maxWidth: { min: 30, max: 100 },
};

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

                if (
                    typeof prefs !== "object" ||
                    prefs === null ||
                    Array.isArray(prefs)
                ) {
                    throw new Error("Invalid preferences format");
                }

                // Validate theme
                if (THEMES.includes(prefs.theme)) {
                    this.theme = prefs.theme;
                }

                // Validate font
                if (FONTS.includes(prefs.font)) {
                    this.font = prefs.font;
                }

                // Validate fontSize
                if (
                    typeof prefs.fontSize === "number" &&
                    Number.isFinite(prefs.fontSize) &&
                    prefs.fontSize >= LIMITS.fontSize.min &&
                    prefs.fontSize <= LIMITS.fontSize.max
                ) {
                    this.fontSize = Math.round(prefs.fontSize);
                }

                // Validate maxWidth
                if (
                    typeof prefs.maxWidth === "number" &&
                    Number.isFinite(prefs.maxWidth) &&
                    prefs.maxWidth >= LIMITS.maxWidth.min &&
                    prefs.maxWidth <= LIMITS.maxWidth.max
                ) {
                    this.maxWidth = Math.round(prefs.maxWidth);
                }
            } catch (e) {
                console.error(
                    "Failed to parse reader preferences from localStorage:",
                    e
                );
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
        this.fontSize = Math.min(
            LIMITS.fontSize.max,
            this.fontSize + LIMITS.fontSize.step
        );
    },

    decreaseFontSize() {
        this.fontSize = Math.max(
            LIMITS.fontSize.min,
            this.fontSize - LIMITS.fontSize.step
        );
    },
} as ReaderStore);
