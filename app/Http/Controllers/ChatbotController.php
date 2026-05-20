<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Services\TuitionResolverService;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $student = Auth::guard('student')->user();
        if (!$student) {
            return response()->json(['reply' => 'يرجى تسجيل الدخول أولاً.']);
        }

        // --- Gather Context Data ---
        $resolution = TuitionResolverService::resolve($student);
        $totalTuition = $resolution['total'];
        
        $paidFull = Payment::where('student_id', $student->id)->whereHas('service', fn($q) => $q->where('name', 'like', '%دفع كامل%'))->where('status', 'paid')->exists();
        $paidInst1 = Payment::where('student_id', $student->id)->whereHas('service', fn($q) => $q->where('name', 'like', '%القسط الأول%'))->where('status', 'paid')->exists();
        $paidInst2 = Payment::where('student_id', $student->id)->whereHas('service', fn($q) => $q->where('name', 'like', '%القسط الثاني%'))->where('status', 'paid')->exists();

        $remaining = $totalTuition;
        if ($paidFull) $remaining = 0;
        elseif ($paidInst1 && $paidInst2) $remaining = 0;
        elseif ($paidInst1) $remaining = $resolution['inst2_amount'];

        $pendingPaymentsCount = Payment::where('student_id', $student->id)->where('status', 'pending')->count();
        $latestPayment = Payment::where('student_id', $student->id)->latest('payment_date')->first();
        $latestPaymentStatus = $latestPayment ? ($latestPayment->status == 'paid' ? 'ناجحة' : ($latestPayment->status == 'pending' ? 'قيد المراجعة' : 'فشلت')) : 'لا يوجد';

        // --- System Prompt ---
        $systemPrompt = "أنت المساعد الذكي الخاص ببوابة طلاب الجامعة. وظيفتك الإجابة على استفسارات الطالب بناءً على بياناته الحقيقية التالية:
- اسم الطالب: {$student->name}
- الكلية: {$student->facultyName()}
- الفرقة: {$student->academic_year}
- المصاريف الدراسية الإجمالية: {$totalTuition} ج.م
- المصاريف المتبقية عليه حالياً: {$remaining} ج.م
- عدد المدفوعات قيد المراجعة (Pending): {$pendingPaymentsCount}
- حالة آخر عملية دفع قام بها: {$latestPaymentStatus}

قواعد الرد:
1. استخدم العامية المصرية الودودة (مثل: أهلاً بيك يا أحمد، مصاريفك كذا، تقدر تدفع من هنا...).
2. إذا سأل 'أنا عليا كام؟' أو 'المصاريف كام؟' أو ما شابه، أخبره بالمبلغ المتبقي عليه ({$remaining} ج.م). إذا كان 0 قل له 'أنت مسدد كل مصاريفك يا بطل'.
3. إذا سأل 'آخر ميعاد للدفع إمتى؟' قل له 'يرجى مراجعة إدارة شئون الطلاب في كليتك لمعرفة المواعيد النهائية بالضبط لتجنب غرامات التأخير'.
4. إذا سأل 'دفعت بس الحالة لسه pending أو قيد المراجعة، أعمل إيه؟' قل له 'عملية الدفع بتاعتك لسه بتتراجع من الشئون المالية، ده بياخد وقت بسيط، متقلقش، تقدر تتابعها من قسم (الأرشيف الرقمي)'.
5. إذا سأل 'أطلع إيصال منين؟' قل له 'من القائمة الجانبية ادخل على (الأرشيف الرقمي) وهتلاقي كل إيصالاتك وتقدر تطبعها'.
6. إذا سأل 'أدفع رسوم الكارنيه إزاي؟' قل له 'من الصفحة الرئيسية، انزل لقسم (شئون طلبة) واختار (رسوم كارنيه) وادفعها بسهولة'.
7. الإجابات يجب أن تكون قصيرة، مركزة، وبدون مقدمات طويلة. لا تخترع معلومات غير موجودة في السياق أعلاه.
";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer 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',
                'Content-Type' => 'application/json',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'google/gemini-2.0-flash-exp:free', // Free high-quality model
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $request->message]
                ],
                'temperature' => 0.3, // Low temperature for factual accuracy
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? 'عفواً، مقدرتش أفهمك كويس، جرب تسألني بطريقة تانية.';
                return response()->json(['reply' => $reply]);
            } else {
                Log::error('OpenRouter API Error: ' . $response->body());
                return response()->json(['reply' => 'معلش، السيرفر عندي فيه ضغط حالياً. جرب تسألني تاني كمان شوية!'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            return response()->json(['reply' => 'معلش، النظام بيواجه مشكلة في الاتصال. يرجى مراجعة شئون الطلاب.'], 500);
        }
    }
}
