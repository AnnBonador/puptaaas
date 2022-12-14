<?php

namespace App\Http\Controllers\Admin\Applicant;

use App\Models\User;
use App\Models\NonAcadAward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NonAcademicApplicant;
use App\Notifications\NonAcademicStatus;
use App\Notifications\StudentApplicantStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;

class NAApplicantsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:non-academic award list', ['only' => ['index', 'overallList', 'view']]);
        $this->middleware('permission:non-academic award edit', ['only' => ['update', 'approved', 'rejected']]);
        $this->middleware('permission:non-academic award delete', ['only' => ['destroy', 'deleteAll']]);
    }

    public function index()
    {
        $nonacad = NonAcadAward::all();
        $total = NonAcademicApplicant::count();

        return view('admin.non-academic-award.index', compact('total', 'nonacad'));
    }

    public function details($nonacad_id, $id)
    {
        $form = NonAcademicApplicant::find($id);
        return view('admin.non-academic-award.show', compact('form'));
    }

    public function overallList()
    {
        $form = NonAcademicApplicant::all();
        return view('admin.non-academic-award.all', compact('form'));
    }

    public function view(Request $request, $id)
    {
        $title = NonAcadAward::where('id', $id)->first();
        $form = NonAcademicApplicant::where('nonacad_id', $id)->get();

        return view('admin.non-academic-award.view', compact('title', 'form'));
    }

    public function approved($nonacad_id, $id)
    {
        $approve = NonAcademicApplicant::find($id);
        $users = User::where('id', $approve->user_id)->get();
        $award = $approve->nonacad->nonacad_code;
        $approve->status = 1;

        $approve->save();

        Notification::send($users, new StudentApplicantStatus($approve->id, $approve->status, $award));
        return redirect()->back();
    }
    public function rejected($nonacad_id, $id)
    {
        $reject = NonAcademicApplicant::find($id);
        $users = User::where('id', $reject->user_id)->get();
        $award = $reject->nonacad->nonacad_code;
        $reject->status = 2;

        $reject->save();

        Notification::send($users, new StudentApplicantStatus($reject->id, $reject->status, $award));
        return redirect()->back();
    }

    public function update(Request $request, $nonacad_id, $id)
    {
        $this->validate($request, [
            'status' => 'required',
            'reason' => 'nullable'
        ]);
        $status = NonAcademicApplicant::findOrFail($id);

        $status->status = $request->status;
        $status->reason = $request->reason;
        $award = $status->nonacad->nonacad_code;

        $users = User::where('id', $status->user_id)->get();
        $status->save();
        if ($request->status == '1' || $request->status == '2') {
            Notification::send($users, new StudentApplicantStatus($status->id, $request->status, $award));
        }

        return redirect()->back()->with('success', 'The Application form updated successfully');
    }

    public function openPdfApproved($nonacad_code)
    {
        $nonacad = NonAcadAward::where('nonacad_code', $nonacad_code)->first();
        $students = NonAcademicApplicant::where('nonacad_id', $nonacad->id)
            ->where('status', '1')
            ->get();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.non-academic-award.student-accepted', array('students' => $students), array('nonacad' => $nonacad));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Accepted' . $nonacad->nonacad_code . '.pdf');
    }

    public function openPdfRejected($nonacad_code)
    {
        $nonacad = NonAcadAward::where('nonacad_code', $nonacad_code)->first();
        $students = NonAcademicApplicant::where('nonacad_id', $nonacad->id)
            ->where('status', '2')
            ->get();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.non-academic-award.student-rejected', array('students' => $students), array('nonacad' => $nonacad));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Rejected' . $nonacad->nonacad_code . '.pdf');
    }

    public function openPdfAll($nonacad_code)
    {
        $nonacad = NonAcadAward::where('nonacad_code', $nonacad_code)->first();
        $students = NonAcademicApplicant::where('nonacad_id', $nonacad->id)
            ->orderBy('year_level', 'asc')
            ->get();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.non-academic-award.student-list', array('students' => $students), array('nonacad' => $nonacad));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Non-Academic-Applicants-' . $nonacad->nonacad_code . '.pdf');
    }

    public function destroy(Request $request)
    {
        $form = NonAcademicApplicant::find($request->form_delete_id);
        $form->delete();
        return redirect()->back()->with('success', 'The Application form move to archive successfully');
    }

    public function deleteAll(Request $request)
    {
        $ids = $request->ids;
        NonAcademicApplicant::whereIn('id', $ids)->delete();
        return response()->json([
            'success' => 'The Application form move to archive successfully'
        ]);
    }
}
