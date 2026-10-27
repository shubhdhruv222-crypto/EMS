document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
  if (!confirm(form.dataset.confirm)) event.preventDefault();
}));
const salaryForm = document.getElementById('salaryForm');
if (salaryForm) {
  const update = () => {
    const total = ['basic','allowances','deductions'].map(k => Number(document.getElementById(k).value) || 0);
    document.getElementById('netPreview').textContent = new Intl.NumberFormat('en-IN', {style:'currency',currency:'INR'}).format(total[0]+total[1]-total[2]);
  };
  salaryForm.addEventListener('input', update); update();
}
