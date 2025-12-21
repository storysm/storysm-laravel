/**
 * @vitest-environment jsdom
 */
import { beforeEach, describe, expect, it, vi } from "vitest";

// 1. Use vi.hoisted to set up the global mock BEFORE the store is imported.
// This block runs before the 'import' below.
const { mockStore } = vi.hoisted(() => {
    const mStore = vi.fn();
    const mEffect = vi.fn((cb) => cb());

    // Define it on both globalThis and window
    const alpineMock = {
        store: mStore,
        effect: mEffect,
    };

    (globalThis as any).Alpine = alpineMock;

    // In JSDOM, window is the same as globalThis, but we ensure it's there
    if (typeof window !== "undefined") {
        (window as any).Alpine = alpineMock;
    }

    return { mockStore: mStore };
});

// 2. Now import the store. Because of vi.hoisted, Alpine is already defined.
import "./reader";

describe("Reader Store", () => {
    const STORAGE_KEY = "reader_prefs";
    let readerStore: any;

    beforeEach(() => {
        // Find the store object passed to Alpine.store("reader", ...)
        const call = mockStore.mock.calls.find((c) => c[0] === "reader");
        if (!call)
            throw new Error("Store 'reader' was not registered with Alpine");

        readerStore = call[1];

        // Reset state for test isolation
        localStorage.clear();
        readerStore.theme = "dark";
        readerStore.font = "sans";
        readerStore.fontSize = 100;
        readerStore.maxWidth = 65;
        readerStore.fullscreen = false;
    });

    it("loads valid settings from localStorage on init", () => {
        const validPrefs = {
            theme: "sepia",
            font: "serif",
            fontSize: 120,
            maxWidth: 80,
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(validPrefs));

        readerStore.init();

        expect(readerStore.theme).toBe("sepia");
        expect(readerStore.fontSize).toBe(120);
        expect(readerStore.font).toBe("serif");
    });

    it("rejects invalid values and falls back to defaults", () => {
        const invalidPrefs = {
            fontSize: 999, // Max is 200
            theme: "wrong",
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(invalidPrefs));

        readerStore.init();

        expect(readerStore.fontSize).toBe(100);
        expect(readerStore.theme).toBe("dark");
    });

    it("persists settings when persist() is called", () => {
        readerStore.theme = "light";
        readerStore.fontSize = 150;

        readerStore.persist();

        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY)!);
        expect(saved.theme).toBe("light");
        expect(saved.fontSize).toBe(150);
    });

    it("toggles fullscreen", () => {
        expect(readerStore.fullscreen).toBe(false);
        readerStore.toggleFullscreen();
        expect(readerStore.fullscreen).toBe(true);
    });

    it("resets to defaults", () => {
        readerStore.theme = "sepia";
        readerStore.resetToDefaults();

        expect(readerStore.theme).toBe("dark");
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY)!);
        expect(saved.theme).toBe("dark");
    });

    it("handles JSON parsing errors", () => {
        localStorage.setItem(STORAGE_KEY, "invalid{json");

        // This should trigger the catch block in the store
        readerStore.init();

        expect(readerStore.theme).toBe("dark");
    });
});
