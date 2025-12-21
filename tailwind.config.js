import typography from "@tailwindcss/typography";
import defaultTheme from "tailwindcss/defaultTheme";
import { colors } from "./colors";
import preset from "./vendor/filament/support/tailwind.config.preset";

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        "./app/Filament/**/*.php",
        "./app/Livewire/**/*.php",
        "./lang/**/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/ts/**/*.ts",
        "./storage/framework/views/*.php",
        "./vendor/awcodes/filament-curator/resources/**/*.blade.php",
        "./vendor/awcodes/filament-tiptap-editor/resources/**/*.blade.php",
        "./vendor/awcodes/overlook/resources/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
    ],

    theme: {
        extend: {
            colors: {
                primary: colors.driftwood,
                secondary: colors.terracotta,
                sepia: colors.sepia,
            },
            typography: (theme) => ({
                sepia: {
                    css: {
                        "--tw-prose-body": theme("colors.sepia[800]"),
                        "--tw-prose-headings": theme("colors.sepia[900]"),
                        "--tw-prose-links": theme("colors.primary[600]"),
                        "--tw-prose-bold": theme("colors.sepia[900]"),
                        "--tw-prose-quotes": theme("colors.sepia[900]"),
                        "--tw-prose-hr": theme("colors.sepia[300]"),
                        "--tw-prose-captions": theme("colors.sepia[800]"),
                    },
                },
            }),
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
                logo: ["Grandstander", ...defaultTheme.fontFamily.serif],
                serif: [
                    "Georgia",
                    "Cambria",
                    "Times New Roman",
                    "Times",
                    "serif",
                ],
                mono: [
                    "ui-monospace",
                    "SFMono-Regular",
                    "Menlo",
                    "Monaco",
                    "Consolas",
                    "monospace",
                ],
            },
        },
    },

    plugins: [typography],
};
