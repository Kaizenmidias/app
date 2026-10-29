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
  const listButton = document.querySelector('#bulk-list-form button');
  const idContainers = [...document.querySelectorAll('.bulk-lead-ids')];
  const sync = () => {
    const selected = boxes.filter((box) => box.checked);
    if (bulk) { bulk.disabled = selected.length === 0; bulk.textContent = selected.length ? `Enviar ${selected.length} para a pipeline` : 'Enviar selecionados para a pipeline'; }
    if (listButton) listButton.disabled = selected.length === 0;
    idContainers.forEach((container) => { container.innerHTML = selected.map((box) => `<input type="hidden" name="lead_ids[]" value="${box.value}">`).join(''); });
    if (all) { all.checked = selected.length > 0 && selected.length === boxes.length; all.indeterminate = selected.length > 0 && selected.length < boxes.length; }
  };
  all?.addEventListener('change', () => { boxes.forEach((box) => { box.checked = all.checked; }); sync(); });
  boxes.forEach((box) => box.addEventListener('change', sync));

  document.getElementById('copy-emails')?.addEventListener('click', async () => {
    const selectedIds = new Set(boxes.filter((box) => box.checked).map((box) => box.value));
    const rows = [...document.querySelectorAll('.lead-row')].filter((row) => !selectedIds.size || selectedIds.has(row.querySelector('.lead-select')?.value));
    const emails = [...new Set(rows.map((row) => row.querySelector('.lead-email')?.dataset.email?.trim()).filter(Boolean))];
    if (!emails.length) return window.alert('Nenhum e-mail disponível para copiar.');
    await navigator.clipboard.writeText(emails.join('; '));
    window.alert(`${emails.length} e-mail(s) copiado(s).`);
  });
})();
