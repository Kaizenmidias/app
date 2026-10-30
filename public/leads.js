(() => {
  const openDialog = (id) => document.getElementById(id)?.showModal();

  document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-dialog-open]');
    if (opener) {
      if (event.target.closest('input, a, button, form, details') && opener.classList.contains('lead-row')) return;
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

  const contextMenu = document.getElementById('lead-context-menu');
  const pipelineForm = contextMenu?.querySelector('[data-menu-pipeline-form]');
  let menuRow = null;
  const closeContextMenu = () => {
    if (!contextMenu || contextMenu.hidden) return;
    contextMenu.hidden = true;
    document.querySelectorAll('[data-lead-menu][aria-expanded="true"]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    menuRow = null;
  };
  const openContextMenu = (row, x, y, trigger = null) => {
    if (!contextMenu || !row) return;
    menuRow = row;
    if (pipelineForm) pipelineForm.action = row.dataset.pipelineUrl;
    contextMenu.hidden = false;
    contextMenu.style.left = '0px';
    contextMenu.style.top = '0px';
    const width = contextMenu.offsetWidth;
    const height = contextMenu.offsetHeight;
    contextMenu.style.left = `${Math.max(8, Math.min(x, window.innerWidth - width - 8))}px`;
    contextMenu.style.top = `${Math.max(8, Math.min(y, window.innerHeight - height - 8))}px`;
    trigger?.setAttribute('aria-expanded', 'true');
    contextMenu.querySelector('button')?.focus();
  };

  document.querySelectorAll('.lead-row').forEach((row) => row.addEventListener('contextmenu', (event) => {
    event.preventDefault();
    openContextMenu(row, event.clientX, event.clientY);
  }));
  document.querySelectorAll('[data-lead-menu]').forEach((button) => button.addEventListener('click', (event) => {
    event.preventDefault();
    event.stopPropagation();
    if (!contextMenu?.hidden && menuRow === button.closest('.lead-row')) return closeContextMenu();
    const rect = button.getBoundingClientRect();
    openContextMenu(button.closest('.lead-row'), rect.right - 220, rect.bottom + 6, button);
  }));
  contextMenu?.addEventListener('click', (event) => {
    const action = event.target.closest('[data-menu-action]')?.dataset.menuAction;
    if (!action || !menuRow) return;
    event.preventDefault();
    const dialogId = action === 'labels' ? menuRow.dataset.labelDialog : menuRow.dataset.leadDialog;
    closeContextMenu();
    openDialog(dialogId);
    if (action === 'funnel') requestAnimationFrame(() => document.getElementById(dialogId)?.querySelector('.lead-stage-checklist')?.scrollIntoView({ behavior: 'smooth', block: 'center' }));
  });
  document.addEventListener('click', (event) => { if (!event.target.closest('#lead-context-menu,[data-lead-menu]')) closeContextMenu(); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeContextMenu(); });
  window.addEventListener('resize', closeContextMenu);
  document.querySelector('.lead-table-wrap')?.addEventListener('scroll', closeContextMenu, { passive: true });

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
