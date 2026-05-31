<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\ExamResult;
use App\Models\IdCard;
use App\Services\PdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct(private readonly PdfService $pdfService) {}

    public function downloadCertificate(int $id): Response
    {
        $certificate = Certificate::where('tenant_id', Auth::user()->tenant_id)
            ->with(['student.user', 'student.class', 'issuedBy'])
            ->findOrFail($id);

        $institution = Auth::user()->institution;

        $html = view('pdf.certificate', [
            'certificate' => $certificate,
            'student' => $certificate->student,
            'institution' => $institution,
        ])->render();

        $pdf = $this->pdfService->downloadFromHtml($html);

        $filename = 'certificate-' . $certificate->certificate_number . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadIdCard(int $id): Response
    {
        $idCard = IdCard::where('tenant_id', Auth::user()->tenant_id)
            ->with(['user.student.class', 'user.teacher', 'user.staff'])
            ->findOrFail($id);

        $institution = Auth::user()->institution;

        $html = view('pdf.id-card', [
            'idCard' => $idCard,
            'user' => $idCard->user,
            'institution' => $institution,
        ])->render();

        $pdf = $this->pdfService->downloadFromHtml($html);

        $filename = 'id-card-' . $idCard->card_number . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadResultSheet(int $studentId, int $examId): Response
    {
        $tenantId = Auth::user()->tenant_id;

        $results = ExamResult::where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->whereHas('examSchedule', fn ($q) => $q->where('exam_id', $examId))
            ->with(['examSchedule.subject', 'examSchedule.exam.academicYear'])
            ->get();

        abort_if($results->isEmpty(), 404, 'No results found.');

        $student = $results->first()->student()->with(['user', 'class', 'section'])->first();
        $exam = $results->first()->examSchedule?->exam;
        $institution = Auth::user()->institution;

        $html = view('pdf.result-sheet', [
            'results' => $results,
            'student' => $student,
            'exam' => $exam,
            'institution' => $institution,
        ])->render();

        $pdf = $this->pdfService->downloadFromHtml($html);

        $studentName = str_replace(' ', '-', strtolower(trim(($student?->user?->first_name ?? '') . '-' . ($student?->user?->last_name ?? ''))));
        $filename = 'result-' . $studentName . '-' . ($exam?->name ? Str::slug($exam->name) : $examId) . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
