(() => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let dragging = null;
    let moving = false;
    const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    const updateColumnSummary = (column) => {
        if (!column) return;
        const cards = [...column.querySelectorAll('.opportunity-card')];
        const count = cards.length;
        const total = cards.reduce((sum, card) => sum + Number(card.dataset.budget || 0), 0);
        const countElement = column.querySelector('[data-column-count]');
        const budgetElement = column.querySelector('[data-column-budget]');
        if (countElement) countElement.textContent = `${count} ${count === 1 ? 'oportunidade' : 'oportunidades'}`;
        if (budgetElement) budgetElement.textContent = money.format(total);
    };

    const moveOpportunity = async (card, targetId) => {
        if (moving || !card || !targetId) return false;
        const sourceColumn = card.closest('.pipeline-column');
        const targetColumn = document.querySelector(`.pipeline-column[data-stage-id="${targetId}"]`);
        if (!sourceColumn || !targetColumn || sourceColumn === targetColumn) return true;

        moving = true;
        card.classList.add('is-dragging');
        const body = new URLSearchParams({ _token: token, _method: 'PATCH', pipeline_stage_id: targetId });
        try {
            const response = await fetch(card.dataset.moveUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                body,
            });
            if (!response.ok) throw new Error('Falha ao mover oportunidade');
            targetColumn.querySelector('.pipeline-column-empty')?.remove();
            targetColumn.querySelector('.pipeline-cards').appendChild(card);
            if (!sourceColumn.querySelector('.opportunity-card')) {
                sourceColumn.querySelector('.pipeline-cards').insertAdjacentHTML('beforeend', '<div class="pipeline-column-empty"><span aria-hidden="true">＋</span><p>Nenhuma oportunidade nesta etapa</p><small>Arraste um card para cá</small></div>');
            }
            updateColumnSummary(sourceColumn);
            updateColumnSummary(targetColumn);
            return true;
        } catch (error) {
            window.dispatchEvent(new CustomEvent('pipeline:error', { detail: error }));
            return false;
        } finally {
            moving = false;
            card.classList.remove('is-dragging');
        }
    };

    document.querySelectorAll('.opportunity-card').forEach((card) => {
        card.addEventListener('dragstart', (event) => {
            dragging = card;
            card.dataset.dragging = 'true';
            card.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.opportunityId);
        });
        card.addEventListener('dragend', () => {
            dragging = null;
            card.classList.remove('is-dragging');
            document.querySelectorAll('.pipeline-cards').forEach((target) => target.classList.remove('is-drop-target'));
            setTimeout(() => delete card.dataset.dragging, 0);
        });
        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            document.getElementById(card.dataset.dialogOpen)?.showModal();
        });
    });

    document.querySelectorAll('.pipeline-cards').forEach((target) => {
        target.addEventListener('dragover', (event) => { event.preventDefault(); target.classList.add('is-drop-target'); });
        target.addEventListener('dragleave', (event) => { if (!target.contains(event.relatedTarget)) target.classList.remove('is-drop-target'); });
        target.addEventListener('drop', async (event) => {
            event.preventDefault();
            target.classList.remove('is-drop-target');
            const column = target.closest('.pipeline-column');
            if (dragging && column) await moveOpportunity(dragging, column.dataset.stageId);
        });
    });

    document.querySelectorAll('[data-dialog-open]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.dragging === 'true') return;
            document.getElementById(button.dataset.dialogOpen)?.showModal();
        });
    });
    document.querySelectorAll('[data-dialog-close]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });
    document.querySelectorAll('.stage-menu').forEach((menu) => {
        menu.addEventListener('click', (event) => event.stopPropagation());
    });
    document.addEventListener('click', () => document.querySelectorAll('.stage-menu[open]').forEach((menu) => { menu.removeAttribute('open'); }));
    window.addEventListener('pipeline:error', () => window.alert('Não foi possível mover a oportunidade. Tente novamente.'));
})();
