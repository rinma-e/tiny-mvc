(() => {
  "use strict";

  function disableTransition() {
    const style = document.createElement("style");
    style.innerHTML =
      "*:not(.keep-transition, .keep-transition-children, .keep-transition-children *), *:not(.keep-transition, .keep-transition-children, .keep-transition-children *)::before, *:not(.keep-transition, .keep-transition-children, .keep-transition-children *)::after {transition: none !important;}";
    style.setAttribute("theme-switch-disable-transition", "true");
    document.head.appendChild(style);
    const clear = () =>
      document
        .querySelectorAll("[theme-switch-disable-transition]")
        .forEach((element) => element.remove());
    return clear;
  }

  const getStoredTheme = () => localStorage.getItem("theme");
  const setStoredTheme = (theme) => localStorage.setItem("theme", theme);

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
      return storedTheme;
    }

    return window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  };

  const setTheme = (theme) => {
    if (theme === "auto") {
      document.documentElement.setAttribute(
        "data-bs-theme",
        window.matchMedia("(prefers-color-scheme: dark)").matches
          ? "dark"
          : "light"
      );
    } else {
      document.documentElement.setAttribute("data-bs-theme", theme);
    }
    const clearStylesTag = disableTransition();
    const clearStyles = window.setTimeout(() => {
      clearStylesTag();
      window.clearTimeout(clearStyles);
    }, 10);
  };

  setTheme(getPreferredTheme());

  const showActiveTheme = (theme) => {
    const themeSwitcher = document.querySelector("#bd-theme");

    if (!themeSwitcher) {
      return;
    }

    // if theme is dark set checked to true
    themeSwitcher.querySelector("input").checked = theme === "dark";

    // if input is checked set svg fill to white (moon color) else orange (sun color)
    const svg = themeSwitcher.querySelector("svg");
    const fill = themeSwitcher.querySelector("input").checked
      ? "white"
      : "orange";
    svg.setAttribute("fill", fill);
    // from themeSwitcher remove class "d-none" to make it visible
    themeSwitcher.classList.remove("d-none");
  };

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", () => {
      const storedTheme = getStoredTheme();
      if (storedTheme !== "light" && storedTheme !== "dark") {
        setTheme(getPreferredTheme());
      }
    });

  window.addEventListener("DOMContentLoaded", () => {
    showActiveTheme(getPreferredTheme());

    // for element with id bd-theme set data-bs-theme-value attribute to light or dark on click
    const themeSwitcher = document.querySelector("#bd-theme input");

    themeSwitcher.addEventListener("click", () => {
      const theme = themeSwitcher.checked ? "dark" : "light";
      themeSwitcher.setAttribute("data-bs-theme-value", theme);
      setStoredTheme(theme);
      setTheme(theme);
      showActiveTheme(theme);
    });
  });
})();
