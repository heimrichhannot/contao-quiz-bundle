var components = {
    "packages": [
        {
            "name": "masonry",
            "main": "masonry-built.js"
        }
    ],
    "shim": {
        "masonry": {
            "exports": "Masonry"
        }
    },
    "baseUrl": "components"
};
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}