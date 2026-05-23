@php $svc = $service ?? null; @endphp
<div class="mb-3">
    <label class="form-label">قسم الخدمة</label>
    <select name="category_group" class="form-select" required>
        @foreach(\App\Support\ServiceCategoryGroups::LABELS as $key => $label)
            <option value="{{ $key }}" @selected(old('category_group', $svc?->category_group ?? 'student_affairs') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">تعليمات للطالب</label>
    <textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $svc?->instructions) }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">أيام التنفيذ المتوقعة</label>
    <input type="number" name="estimated_days" class="form-control" min="1" max="90" value="{{ old('estimated_days', $svc?->estimated_days ?? 3) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">حقول مطلوبة (JSON)</label>
    <textarea name="required_fields_json" class="form-control font-monospace small" rows="2" placeholder='[{"key":"subject","label":"المادة","required":true}]'>{{ old('required_fields_json', $svc?->required_fields ? json_encode($svc->required_fields, JSON_UNESCAPED_UNICODE) : '') }}</textarea>
</div>
