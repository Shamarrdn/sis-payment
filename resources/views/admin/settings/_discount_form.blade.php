<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-bold">اسم الإعفاء <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', optional($d)->name) }}" required placeholder="مثال: إعفاء أبناء الشهداء">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">فئة الطالب <span class="text-danger">*</span></label>
        <input type="text" name="category" class="form-control" value="{{ old('category', optional($d)->category) }}" required placeholder="يطابق special_category للطالب">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">نوع الإعفاء <span class="text-danger">*</span></label>
        <select name="type" class="form-select" required>
            <option value="percentage" {{ optional($d)->type === 'percentage' ? 'selected' : '' }}>نسبة مئوية (%)</option>
            <option value="partial"    {{ optional($d)->type === 'partial'    ? 'selected' : '' }}>مبلغ ثابت مخصوم</option>
            <option value="full"       {{ optional($d)->type === 'full'       ? 'selected' : '' }}>إعفاء كامل (مجاناً)</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">القيمة <span class="text-danger">*</span></label>
        <input type="number" name="value" step="0.01" min="0" class="form-control" value="{{ old('value', optional($d)->value) }}" placeholder="0 = إعفاء كامل">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">جهة الاعتماد</label>
        <input type="text" name="approving_authority" class="form-control" value="{{ old('approving_authority', optional($d)->approving_authority) }}" placeholder="مثال: صندوق تكريم الشهداء">
    </div>
    <div class="col-12">
        <label class="form-label fw-bold">ملاحظات</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', optional($d)->notes) }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="discountActive"
                   {{ (!$d || optional($d)->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="discountActive">تفعيل هذا الإعفاء</label>
        </div>
    </div>
</div>
