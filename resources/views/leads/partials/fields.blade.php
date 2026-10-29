@php($raw = $lead?->raw_data ?? [])
<div class="lead-form-grid">
    <label>Nome do contato ou empresa<input name="name" required value="{{ old('name', $lead?->name) }}"></label>
    <label>Empresa<input name="company" value="{{ old('company', $raw['manual_company'] ?? '') }}"></label>
    <label>Categoria<input name="category" value="{{ old('category', $lead?->category) }}"></label>
    <label>Setor<input name="sector" value="{{ old('sector', $lead?->sector) }}"></label>
    <label>Lista<select name="lead_list_id"><option value="">Sem lista</option>@foreach($lists as $list)<option value="{{ $list->id }}" @selected((string)old('lead_list_id', $lead?->lead_list_id) === (string)$list->id)>{{ $list->name }}</option>@endforeach</select></label>
    <label>Responsável<select name="responsible_user_id"><option value="">Sem responsável</option>@foreach($assignees as $assignee)<option value="{{ $assignee->id }}" @selected((string)old('responsible_user_id', $lead?->responsible_user_id) === (string)$assignee->id)>{{ $assignee->name }}</option>@endforeach</select></label>
    <label>Telefone / WhatsApp<input name="phone" value="{{ old('phone', $lead?->phone) }}"></label>
    <label>E-mail<input type="email" name="email" value="{{ old('email', $lead?->email) }}"></label>
    <label>Site<input type="url" name="website" value="{{ old('website', $lead?->website) }}"></label>
    <label>Instagram<input type="url" name="instagram" value="{{ old('instagram', $lead?->instagram ?? data_get($raw, 'instagram')) }}" placeholder="https://instagram.com/..."></label>
    <label class="lead-field-wide">LinkedIn<input type="url" name="linkedin" value="{{ old('linkedin', $lead?->linkedin ?? data_get($raw, 'linkedin')) }}" placeholder="https://linkedin.com/..."></label>
    <label>Número de funcionários<input type="number" min="0" name="employee_count" value="{{ old('employee_count', $lead?->employee_count) }}"></label>
    <label>Receita estimada<input type="number" min="0" step="0.01" name="estimated_revenue" value="{{ old('estimated_revenue', $lead?->estimated_revenue) }}"></label>
    <label>Valor do orçamento<input type="number" min="0" step="0.01" name="budget_value" value="{{ old('budget_value', $lead?->budget_value) }}"></label>
    <label>Negociação<input value="{{ $lead?->opportunity?->stage?->name ?? 'Ainda não enviada à pipeline' }}" readonly></label>
    <label class="lead-field-wide">Endereço<input name="address" value="{{ old('address', $lead?->address) }}"></label>
    <label class="lead-field-wide">Etiquetas<input name="tags_text" value="{{ old('tags_text', implode(', ', $lead?->tags ?? [])) }}" placeholder="Separe as etiquetas por vírgula"></label>
    <label class="lead-field-wide">Observações<textarea name="observations">{{ old('observations', $raw['manual_observations'] ?? '') }}</textarea></label>
    @if($lead)<label class="lead-field-wide">Nova anotação<textarea name="note" placeholder="Registre uma interação ou informação importante"></textarea></label>@endif
</div>
@if($lead)
<section class="lead-history">
    <h3>Histórico de atividade</h3>
    @forelse($lead->activities as $activity)
        <article><strong>{{ $activity->description }}</strong><time>{{ $activity->created_at->format('d/m/Y H:i') }}</time></article>
    @empty
        <p>Nenhuma atividade registrada ainda.</p>
    @endforelse
    @foreach($lead->notes as $note)
        <article><strong>Anotação: {{ $note->content }}</strong><time>{{ $note->created_at->format('d/m/Y H:i') }}</time></article>
    @endforeach
</section>
@endif
