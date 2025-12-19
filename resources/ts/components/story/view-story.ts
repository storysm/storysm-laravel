import { ReaderStore } from "../../stores/reader";

const Alpine = window.Alpine;

Alpine.data("viewStory", () => ({
    reader: Alpine.store("reader") as ReaderStore,
    getContainerDynamicClasses() {
        const fullscreen = this.reader.fullscreen;
        const theme = this.reader.theme;
        return [
            fullscreen
                ? "fixed inset-0 z-30 overflow-y-auto p-4 md:p-12 lg:col-span-12"
                : "lg:col-start-3 lg:col-span-8",
            fullscreen && theme === "light" ? "bg-white" : "",
            fullscreen && theme === "sepia" ? "bg-sepia-100" : "",
            fullscreen && theme === "dark" ? "bg-gray-900" : "",
        ]
            .filter(Boolean)
            .join(" ");
    },
    getProseDynamicClasses() {
        const fullscreen = this.reader.fullscreen;
        const theme = this.reader.theme;
        const font = this.reader.font;
        return [
            !fullscreen ? "prose dark:prose-invert" : "",
            fullscreen && theme === "light" ? "prose" : "",
            fullscreen && theme === "sepia" ? "prose prose-sepia" : "",
            fullscreen && theme === "dark" ? "prose prose-invert" : "",
            fullscreen && font === "sans" ? "font-sans" : "",
            fullscreen && font === "serif" ? "font-serif" : "",
            fullscreen && font === "mono" ? "font-mono" : "",
        ]
            .filter(Boolean)
            .join(" ");
    },
    getProseDynamicStyle() {
        const fullscreen = this.reader.fullscreen;
        const fontSize = this.reader.fontSize;
        const maxWidth = this.reader.maxWidth;
        return fullscreen
            ? `font-size: ${fontSize}%; max-width: ${maxWidth}ch;`
            : "";
    },
}));
