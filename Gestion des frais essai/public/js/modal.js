document.addEventListener("DOMContentLoaded", function() {
    // =======================
    // MODALE D'AJOUT D'UTILISATEUR
    // =======================
    const openCreateModalBtn = document.getElementById("openModalBtn");
    const closeCreateModalBtn = document.getElementById("closeCreateModalBtn");
    const createUserModal = document.getElementById("createUserModal");
    const createModalContent = document.getElementById("createModalContent");

    if (openCreateModalBtn) {
        openCreateModalBtn.addEventListener("click", function() {
            createUserModal.classList.remove("hidden");

            fetch("create_usr.php")
                .then(response => response.text())
                .then(data => {
                    createModalContent.innerHTML = data;
                    setupCreateForm(); // Initialise la soumission en AJAX
                })
                .catch(error => console.error("Erreur de chargement:", error));
        });
    }

    if (closeCreateModalBtn) {
        closeCreateModalBtn.addEventListener("click", function() {
            createUserModal.classList.add("hidden");
            createModalContent.innerHTML = "";
        });

        createUserModal.addEventListener("click", function(event) {
            if (event.target === createUserModal) {
                createUserModal.classList.add("hidden");
                createModalContent.innerHTML = "";
            }
        });
    }

    function setupCreateForm() {
        const createForm = document.getElementById("createUserForm");
        if (createForm) {
            createForm.addEventListener("submit", function(event) {
                event.preventDefault();

                const formData = new FormData(createForm);

                fetch("insert_usr.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.getElementById("createMessage");

                    if (data.status === "success") {
                        messageDiv.innerHTML = `<div class="text-green-600">${data.message}</div>`;
                        setTimeout(() => {
                            createUserModal.classList.add("hidden");
                            location.reload();
                        }, 1500);
                    } else {
                        messageDiv.innerHTML = `<div class="text-red-600">${data.message}</div>`;
                    }
                })
                .catch(error => console.error("Erreur d'envoi:", error));
            });
        }
    }

    // =======================
    // MODALE D'ÉDITION D'UTILISATEUR
    // =======================
    const editButtons = document.querySelectorAll(".edit-btn");
    const editUserModal = document.getElementById("editUserModal");
    const editModalContent = document.getElementById("editModalContent");
    const closeEditModalBtn = document.getElementById("closeEditModalBtn");

    editButtons.forEach(button => {
        button.addEventListener("click", function() {
            const userId = this.getAttribute("data-id");
            editUserModal.classList.remove("hidden");

            fetch(`edit_usr.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    editModalContent.innerHTML = data;
                    setupEditForm(); // Initialise la soumission en AJAX
                })
                .catch(error => console.error("Erreur de chargement:", error));
        });
    });

    if (closeEditModalBtn) {
        closeEditModalBtn.addEventListener("click", function() {
            editUserModal.classList.add("hidden");
            editModalContent.innerHTML = "";
        });

        editUserModal.addEventListener("click", function(event) {
            if (event.target === editUserModal) {
                editUserModal.classList.add("hidden");
                editModalContent.innerHTML = "";
            }
        });
    }

    function setupEditForm() {
        const editForm = document.getElementById("editUserForm");
        if (editForm) {
            editForm.addEventListener("submit", function(event) {
                event.preventDefault();

                const formData = new FormData(editForm);

                fetch("update_usr.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById("editMessage").innerHTML = data;
                    setTimeout(() => {
                        editUserModal.classList.add("hidden");
                        location.reload();
                    }, 1500);
                })
                .catch(error => console.error("Erreur d'envoi:", error));
            });
        }
    }
});