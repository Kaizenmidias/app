(() => {
  const openDialog = (id) => document.getElementById(id)?.showModal();

  document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-dialog-open]');
    if (opener) {
      if (event.target.closest('input, a, form, details') && opener.classList.contains('lead-row')) return;
      event.preventDefault();
      openDialog(opener.dataset.dialogOpen);
      return;
    }
    const closer = event.target.closest('[data-dialog-close]');
    if (closer) closer.closest('dialog')?.close();
  });

  document.addEventListener('keydown', (event) => {
    if ((event.key === 'Enter' || event.key === ' ') && event.target.matches('.lead-row')) {
      event.preventDefault();
      openDialog(event.target.dataset.dialogOpen);
    }
  });

  document.querySelectorAll('dialog').forEach((dialog) => dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  }));

  const boxes = [...document.querySelectorAll('.lead-select')];
  const all = document.getElementById('select-all');
  const bulk = document.getElementById('bulk-pipeline');
  const ids = document.getElementById('bulk-lead-ids');
  const sync = () => {
    const selected = boxes.filter((box) => box.checked);
    if (bulk) { bulk.disabled = selected.length === 0; bulk.textContent = selected.length ? `Enviar ${selected.length} para a pipeline` : 'Enviar selecionados para a pipeline'; }
    if (ids) ids.innerHTML = selected.map((box) => `<input type="hidden" name="lead_ids[]" value="${box.value}">`).join('');
    if (all) { all.checked = selected.length > 0 && selected.length === boxes.length; all.indeterminate = selected.length > 0 && selected.length < boxes.length; }
  };
  all?.addEventListener('change', () => { boxes.forEach((box) => { box.checked = all.checked; }); sync(); });
  boxes.forEach((box) => box.addEventListener('change', sync));
})();
