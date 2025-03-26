document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll(".rembourse-checkbox");
    const totalRembourseElem = document.getElementById("totalRembourse");

    function updateTotalsAndMotifs() {
        let total = 0;

        checkboxes.forEach(cb => {
            const lineId = cb.value;
            const motifInput = document.querySelector(`input[name='motif[${lineId}]']`);

            if (cb.checked) {
                total += parseFloat(cb.dataset.total);
                if (motifInput) {
                    motifInput.disabled = true;
                    motifInput.classList.add("opacity-50", "cursor-not-allowed");
                }
            } else {
                if (motifInput) {
                    motifInput.disabled = false;
                    motifInput.classList.remove("opacity-50", "cursor-not-allowed");
                }
            }
        });

        if (totalRembourseElem) {
            totalRembourseElem.textContent = total.toFixed(2);
        }
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", updateTotalsAndMotifs);
    });

    updateTotalsAndMotifs();
});