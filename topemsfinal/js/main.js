

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       MOBILE SIDEBAR
    ===================================================== */

    const menuToggle =
        document.querySelector("#menuToggle");

    const sidebar =
        document.querySelector(".sidebar");

    if (menuToggle && sidebar) {

        menuToggle.addEventListener("click", function () {

            sidebar.classList.toggle("open");

        });

    }


    /* =====================================================
       SHOW / HIDE PASSWORD
    ===================================================== */

    const showPassword =
        document.querySelector("#showPassword");

    const passwordInput =
        document.querySelector("#password");


    if (showPassword && passwordInput) {

        showPassword.addEventListener(
            "click",
            function () {

                if (passwordInput.type === "password") {

                    passwordInput.type = "text";

                    showPassword.textContent = "🙈";

                } else {

                    passwordInput.type = "password";

                    showPassword.textContent = "👁";

                }

            }
        );

    }


    /* =====================================================
       MODALS
    ===================================================== */

    const openButtons =
        document.querySelectorAll("[data-modal-open]");


    openButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const modalID =
                    button.getAttribute("data-modal-open");

                const modal =
                    document.getElementById(modalID);

                if (modal) {

                    modal.style.display = "block";

                }

            }
        );

    });


    const closeButtons =
        document.querySelectorAll("[data-modal-close]");


    closeButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const modal =
                    button.closest(".modal-overlay");

                if (modal) {

                    modal.style.display = "none";

                }

            }
        );

    });


    /* =====================================================
       CLOSE MODAL WHEN CLICKING OUTSIDE
    ===================================================== */

    const overlays =
        document.querySelectorAll(".modal-overlay");


    overlays.forEach(function (overlay) {

        overlay.addEventListener(
            "click",
            function (event) {

                if (event.target === overlay) {

                    overlay.style.display = "none";

                }

            }
        );

    });


    /* =====================================================
       EMPLOYEE SEARCH
    ===================================================== */

    const searchInput =
        document.querySelector("#employeeSearch");

    const employeeTable =
        document.querySelector("#employeeTable");


    if (searchInput && employeeTable) {

        searchInput.addEventListener(
            "input",
            function () {

                const search =
                    searchInput.value.toLowerCase();

                const rows =
                    employeeTable
                        .querySelectorAll("tbody tr");


                rows.forEach(function (row) {

                    const text =
                        row.textContent.toLowerCase();


                    if (text.includes(search)) {

                        row.style.display = "";

                    } else {

                        row.style.display = "none";

                    }

                });

            }
        );

    }



});
