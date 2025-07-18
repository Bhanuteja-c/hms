document.getElementById("riskForm").addEventListener("submit", function (event) {
  event.preventDefault();

  const button = event.target.querySelector("button");
  const spinner = document.getElementById("loadingSpinner");
  const resultModal = document.getElementById("resultModal");

  // Show loading spinner
  button.disabled = true;
  spinner.classList.remove("hidden");

  const hasHighBP = +document.getElementById("has_high_bp").value;
  const bp_sys = hasHighBP ? 140 : 120;
  const bp_dia = hasHighBP ? 90 : 80;

  const data = {
    age: +document.getElementById("age").value,
    gender: +document.getElementById("gender").value,
    bp_sys,
    bp_dia,
    cholesterol: +document.getElementById("cholesterol").value,
    glucose: +document.getElementById("glucose").value,
    bmi: +document.getElementById("bmi").value,
    heart_rate: +document.getElementById("heart_rate").value,
  };

  fetch("/predict", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  })
    .then((res) => res.json())
    .then((res) => {
      const { risk, confidence, class0, class1 } = res;

      const riskColor = risk === "High" ? "text-red-600" : "text-green-600";
      const barColor = risk === "High" ? "bg-red-500" : "bg-green-500";

     document.getElementById("modalContent").innerHTML = `
  <div class="space-y-2">
    <p class="${riskColor} text-2xl font-bold flex items-center justify-center gap-2">
      <i data-lucide="activity"></i> Risk: ${risk}
    </p>

    <div class="space-y-1 text-sm text-gray-700">
      <p class="flex items-center justify-center gap-2">
        <i data-lucide="percent-circle"></i> Probability of Disease:
        <span class="font-semibold">${(class1 * 100).toFixed(1)}%</span>
      </p>
      <p class="flex items-center justify-center gap-2">
        <i data-lucide="shield-check"></i> Probability of No Disease:
        <span class="font-semibold">${(class0 * 100).toFixed(1)}%</span>
      </p>
    </div>

    <div class="relative w-full h-5 mt-2 bg-gray-200 rounded-full overflow-hidden">
      <div class="${barColor} h-full transition-all duration-500" style="width: ${(class1 * 100).toFixed(1)}%"></div>
      <span class="absolute left-2 text-white text-xs font-bold top-0.5">${(class1 * 100).toFixed(1)}%</span>
    </div>
  </div>
`;

lucide.createIcons(); // Refresh icons after injection


      // Show modal
      resultModal.classList.remove("hidden");
    })
    .catch((err) => {
      console.error("Error:", err);
      alert("Something went wrong!");
    })
    .finally(() => {
      spinner.classList.add("hidden");
      button.disabled = false;
    });
});

// Close modal function
function closeModal() {
  document.getElementById("resultModal").classList.add("hidden");
}
