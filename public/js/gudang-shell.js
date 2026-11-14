document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("g2bSidebar");
    const toggle = document.querySelector("[data-sidebar-toggle]");
    const backdrop = document.querySelector(".g2b-backdrop");
    if (!sidebar || !toggle || !backdrop) return;
    const close = () => {
        sidebar.classList.remove("is-open");
        backdrop.classList.remove("is-visible");
        document.body.classList.remove("g2b-no-scroll");
        toggle.setAttribute("aria-expanded", "false");
    };
    const open = () => {
        sidebar.classList.add("is-open");
        backdrop.classList.add("is-visible");
        document.body.classList.add("g2b-no-scroll");
        toggle.setAttribute("aria-expanded", "true");
        sidebar.querySelector(".g2b-sidebar-close")?.focus();
    };
    toggle.addEventListener("click", () =>
        sidebar.classList.contains("is-open") ? close() : open(),
    );
    document.querySelectorAll("[data-sidebar-close]").forEach((n) =>
        n.addEventListener("click", () => {
            close();
            toggle.focus();
        }),
    );
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && sidebar.classList.contains("is-open")) {
            close();
            toggle.focus();
        }
    });
    window.matchMedia("(min-width: 992px)").addEventListener("change", (e) => {
        if (e.matches) close();
    });
});
