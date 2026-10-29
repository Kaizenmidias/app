@php($raw = $lead?->raw_data ?? [])
<div class="lead-form-grid">
    <label>Nome do contato ou empresa<input name="name" required value="{{ old('name', $lead?->name) }}"></label>
    <label>Empresa<input name="company" value="{{ old('company', $raw['manual_company'] ?? '') }}"></label>
    <label>Categoria<input name="category" value="{{ old('category', $lead?->category) }}"></label>
    <label>Lista<select name="lead_list_id"><option value="">Sem lista</option>@foreach($lists as $list)<option value="{{ $list->id }}" @selected((string)old('lead_list_id', $lead?->lead_list_id) === (string)$list->id)>{{ $list->name }}</option>@endforeach</select></label>
    <label>Telefone<input name="phone" value="{{ old('phone', $lead?->phone) }}"></label>
    <label>E-mail<input type="email" name="email" value="{{ old('email', $lead?->email) }}"></label>
    <label class="lead-field-wide">Site<input type="url" name="website" value="{{ old('website', $lead?->website) }}"></label>
    <label class="lead-field-wide">Endereço<input name="address" value="{{ old('address', $lead?->address) }}"></label>
    <label class="lead-field-wide">Observações<textarea name="observations">{{ old('observations', $raw['manual_observations'] ?? '') }}</textarea></label>
</div>
