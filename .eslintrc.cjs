// eslint-disable-next-line no-undef
module.exports = {
    root: true,
    env: { browser: true, es2020: true },
    extends: ["eslint:recommended", "plugin:@typescript-eslint/recommended"],
    parser: "@typescript-eslint/parser",
    rules: {
        "@typescript-eslint/ban-ts-comment": "off",
    },
};
