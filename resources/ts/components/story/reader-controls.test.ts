/**
 * @vitest-environment jsdom
 */
import { beforeEach, describe, expect, it, vi } from "vitest";

const { mockData } = vi.hoisted(() => {
    const mData = vi.fn();
    const alpineMock = {
        data: mData,
        store: vi.fn(),
    };

    (globalThis as any).Alpine = alpineMock;
    if (typeof window !== "undefined") {
        (window as any).Alpine = alpineMock;
    }

    return { mockData: mData };
});

// This import triggers the Alpine.data call immediately
import "./reader-controls";

describe("reader-controls", () => {
    // 1. Extract the factory ONCE here, before mocks are cleared
    const call = mockData.mock.calls.find((c) => c[0] === "readerControls");
    if (!call) {
        throw new Error("Alpine.data('readerControls', ...) was not called");
    }
    const componentFactory = call[1];

    let component: any;

    beforeEach(() => {
        // We can safely clear mocks now, because we already captured componentFactory
        vi.clearAllMocks();

        // 2. Instantiate the component logic
        component = componentFactory();

        // Mock Alpine internal properties
        component.$store = {
            reader: { fullscreen: false },
        };
        component.$watch = vi.fn();

        // Mock requestAnimationFrame
        vi.stubGlobal("requestAnimationFrame", (fn: FrameRequestCallback) =>
            fn(0)
        );

        // Reset state
        document.documentElement.scrollTop = 0;
        component.lastScrollY = 0;
        component.showToolbar = true;
        component.ticking = false;
    });

    it("should hide toolbar when scrolling down past 100px", () => {
        component.lastScrollY = 0;
        document.documentElement.scrollTop = 150;

        component.handleScroll(new Event("scroll"));

        expect(component.showToolbar).toBe(false);
        expect(component.lastScrollY).toBe(150);
    });

    it("should show toolbar when scrolling up", () => {
        component.lastScrollY = 500;
        component.showToolbar = false;
        document.documentElement.scrollTop = 500;

        document.documentElement.scrollTop = 400;
        component.handleScroll(new Event("scroll"));

        expect(component.showToolbar).toBe(true);
        expect(component.lastScrollY).toBe(400);
    });

    it("should keep toolbar visible near top", () => {
        component.lastScrollY = 0;
        document.documentElement.scrollTop = 50;

        component.handleScroll(new Event("scroll"));

        expect(component.showToolbar).toBe(true);
    });
});
