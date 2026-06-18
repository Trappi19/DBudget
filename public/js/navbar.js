let dark = document.getElementById("dark");
let navbar = document.getElementById("side-menu");

let shadow_color_dark = "#000000ab";
let shadow_color_light = "#00000000";

function show_navbar() {

    navbar.style.left = "0px";
    dark.style.backgroundColor = shadow_color_dark;
    dark.style.zIndex = "90";

    dark.addEventListener("click", hide_navbar);
}

function hide_navbar() {

    navbar.style.left = "-175px";
    dark.style.backgroundColor = shadow_color_light;
    dark.style.zIndex = "-1";

    dark.removeEventListener("click", hide_navbar);
}

// Security: close navbar when the window is resized
window.addEventListener("resize", () => {
    hide_navbar()
    if (window.innerWidth > 768) navbar.style.left = "0px";
});