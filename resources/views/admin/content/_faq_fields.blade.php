<div class="mb-3">
    <label class="form-label">التصنيف</label>
    <input type="text" name="category" class="form-control" value="{{ old('category', $faq?->category ?? 'عام') }}" required placeholder="خدمات، مصروفات، عام...">
</div>
<div class="mb-3">
    <label class="form-label">السؤال</label>
    <input type="text" name="question" class="form-control" value="{{ old('question', $faq?->question) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">الإجابة</label>
    <textarea name="answer" class="form-control" rows="4" required>{{ old('answer', $faq?->answer) }}</textarea>
</div>
<div class="form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active{{ $faq?->id ?? 'new' }}" @checked(old('is_active', $faq?->is_active ?? true))>
    <label class="form-check-label" for="active{{ $faq?->id ?? 'new' }}">نشط</label>
</div>
