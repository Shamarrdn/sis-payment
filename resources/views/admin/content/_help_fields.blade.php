<div class="mb-3">
    <label class="form-label">التصنيف</label>
    <input type="text" name="category" class="form-control" value="{{ old('category', $article?->category ?? 'الخدمات') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">العنوان</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $article?->title) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">المحتوى (خطوات)</label>
    <textarea name="content" class="form-control" rows="6" required>{{ old('content', $article?->content) }}</textarea>
</div>
<div class="form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $article?->is_active ?? true))>
    <label class="form-check-label">نشط</label>
</div>
