<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApplicantController extends Controller
{
    public function index()
    {
        $applicants = Applicant::withCount('examAttempts')
            ->latest()
            ->paginate(30);

        return Inertia::render('Admin/Applicants/Index', [
            'applicants' => $applicants,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Applicants/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email',
            'identifier' => 'required|string|size:12|unique:applicants,identifier',
            'address' => 'required|string',
            'phone' => 'required|string',
            'graduate_organization' => 'required|string',
            'graduate_year' => 'required|string',
            'speciality' => 'required|string',
            'language' => 'required|in:kz,ru,en',
            'verified' => 'boolean',
            'document_front' => 'nullable|image|max:2048',
            'document_back' => 'nullable|image|max:2048',
            'diplom' => 'nullable|image|max:2048',
            'certificate' => 'nullable|image|max:2048',
            'photo' => 'nullable|image|max:2048',
        ]);

        $applicant = Applicant::create($validated);

        // Загрузка файлов
        if ($request->hasFile('document_front')) {
            $path = $request->file('document_front')->store('applicants/documents', 'public');
            $applicant->update(['document_front' => $path]);
        }

        if ($request->hasFile('document_back')) {
            $path = $request->file('document_back')->store('applicants/documents', 'public');
            $applicant->update(['document_back' => $path]);
        }

        if ($request->hasFile('diplom')) {
            $path = $request->file('diplom')->store('applicants/diploms', 'public');
            $applicant->update(['diplom' => $path]);
        }

        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('applicants/certificates', 'public');
            $applicant->update(['certificate' => $path]);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('applicants/photos', 'public');
            $applicant->update(['photo' => $path]);
        }

        return redirect()->route('admin.applicants.index')
            ->with('success', 'Абитуриент успешно зарегистрирован');
    }

    public function show(Applicant $applicant)
    {
        $applicant->load(['examAttempts.exam', 'examAttempts.result']);

        return Inertia::render('Admin/Applicants/Show', [
            'applicant' => $applicant,
        ]);
    }

    public function edit(Applicant $applicant)
    {
        return Inertia::render('Admin/Applicants/Edit', [
            'applicant' => $applicant,
        ]);
    }

    public function update(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email,'.$applicant->id,
            'identifier' => 'required|string|size:12|unique:applicants,identifier,'.$applicant->id,
            'address' => 'required|string',
            'phone' => 'required|string',
            'graduate_organization' => 'required|string',
            'graduate_year' => 'required|string',
            'speciality' => 'required|string',
            'language' => 'required|in:kz,ru,en',
            'verified' => 'boolean',
            'document_front' => 'nullable|image|max:2048',
            'document_back' => 'nullable|image|max:2048',
            'diplom' => 'nullable|image|max:2048',
            'certificate' => 'nullable|image|max:2048',
            'photo' => 'nullable|image|max:2048',
        ]);

        $applicant->update($validated);

        // Обновление файлов
        if ($request->hasFile('document_front')) {
            $path = $request->file('document_front')->store('applicants/documents', 'public');
            $applicant->update(['document_front' => $path]);
        }

        if ($request->hasFile('document_back')) {
            $path = $request->file('document_back')->store('applicants/documents', 'public');
            $applicant->update(['document_back' => $path]);
        }

        if ($request->hasFile('diplom')) {
            $path = $request->file('diplom')->store('applicants/diploms', 'public');
            $applicant->update(['diplom' => $path]);
        }

        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('applicants/certificates', 'public');
            $applicant->update(['certificate' => $path]);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('applicants/photos', 'public');
            $applicant->update(['photo' => $path]);
        }

        return redirect()->route('admin.applicants.index')
            ->with('success', 'Данные абитуриента обновлены');
    }

    public function destroy(Applicant $applicant)
    {
        if ($applicant->examAttempts()->count() > 0) {
            return back()->with('error', 'Невозможно удалить абитуриента с попытками экзаменов');
        }

        $applicant->delete();

        return redirect()->route('admin.applicants.index')
            ->with('success', 'Абитуриент удален');
    }
}
