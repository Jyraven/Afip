document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('editUserForm');
    const submitBtn = document.getElementById('submitBtn');

    if (!form || !submitBtn) return;

    // État initial
    const initialData = {
        email: form.email.value,
        firstname: form.firstname.value,
        lastname: form.lastname.value,
        role: form.role.value,
        password: '' // champ vide de base
    };

    function checkChanges() {
        const passwordInput = form.querySelector('input[name="password"]');
    
        const isChanged =
            form.email.value !== initialData.email ||
            form.firstname.value !== initialData.firstname ||
            form.lastname.value !== initialData.lastname ||
            form.role.value !== initialData.role ||
            (passwordInput && passwordInput.value !== initialData.password);
    
        submitBtn.disabled = !isChanged;
        submitBtn.classList.toggle('opacity-50', !isChanged);
        submitBtn.classList.toggle('cursor-not-allowed', !isChanged);
    }    

    // Listener sur tous les inputs/select
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', checkChanges);
    });

    // Au chargement, on désactive le bouton
    checkChanges();
});