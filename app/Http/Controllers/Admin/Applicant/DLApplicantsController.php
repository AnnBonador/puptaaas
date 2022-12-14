<?php

namespace App\Http\Controllers\Admin\Applicant;

use App\Models\User;
use App\Models\Courses;
use App\Models\Summary;
use Illuminate\Http\Request;
use App\Models\StudentApplicant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StudentApplicantStatus;

class DLApplicantsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:menu academic awards', ['only' => ['index', 'achieversView', 'approved', 'rejected', 'studentApplicationView', 'update', 'openPdfApproved', 'openPdfRejected', 'openPdfAll', 'overallList']]);
        $this->middleware('permission:deans list list', ['only' => ['index', 'achieversView', 'approved', 'rejected', 'studentApplicationView', 'update', 'openPdfApproved', 'openPdfRejected', 'openPdfAll', 'overallList']]);
        $this->middleware('permission:deans list edit', ['only' => ['update', 'approved', 'rejected']]);
        $this->middleware('permission:deans list delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        $pending = StudentApplicant::where('award_applied', '2')->where('status', '0')->count();
        $courses = Courses::all();
        return view('admin.deans-list-award.index', compact('courses', 'pending'));
    }

    public function achieversView(Request $request, $course_code)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        $model = StudentApplicant::with('users', 'courses')
            ->where('student_applicants.course_id', $courses->id)
            ->where('award_applied', '2')
            ->select('student_applicants.*');

        if ($request->ajax()) {
            if ($request->get('status') == '0' || $request->get('status') == '1' || $request->get('status') == '2') {
                $model->where('status', $request->get('status'))->get();
            }

            if ($request->get('year') == '2nd-Year' || $request->get('year') == '3rd-Year' || $request->get('year') == '4th-Year' || $request->get('year') == '5th-Year') {
                $year = str_replace('-', ' ', $request->get('year'));
                $model->where('year_level', $year)->get();
            }

            return DataTables::eloquent($model)
                ->addColumn('checkbox', function (StudentApplicant $stud) {
                    return '<input type="checkbox" name="form_checkbox" data-id="' . $stud['id'] . '">';
                })
                ->addColumn('studno', function (StudentApplicant $stud) {
                    return $stud->users->stud_num;
                })
                ->addColumn('fname', function (StudentApplicant $stud) {
                    return $stud->users->first_name;
                })
                ->addColumn('lname', function (StudentApplicant $stud) {
                    return $stud->users->last_name;
                })
                ->addColumn('course', function (StudentApplicant $stud) {
                    return $stud->courses->course_code;
                })
                ->addColumn('image', function ($data) {
                    $url = asset('uploads/' . $data->image);
                    return '<img src="' . $url . '" class="img-thumbnail img-circle"
                                    width="50" alt="Image">';
                })
                ->addColumn('status', function (StudentApplicant $data) {
                    return view('admin.deans-list-award.action.status', compact('data'));
                })
                ->addColumn('action', function ($data) {
                    return view('admin.deans-list-award.action.buttons', compact('data'));
                })
                ->rawColumns(['checkbox', 'image', 'status', 'action'])
                ->make(true);
        }

        return view('admin.deans-list-award.view', compact('courses'));
    }

    public function approved($course_code, $id)
    {
        $approve = StudentApplicant::find($id);
        $users = User::where('id', $approve->user_id)->get();
        $award = $approve->award->acad_code;
        $approve->status = 1;

        $approve->save();

        Notification::send($users, new StudentApplicantStatus($approve->id, $approve->status, $award));
        return redirect()->back();
    }
    public function rejected($course_code, $id)
    {
        $reject = StudentApplicant::find($id);
        $users = User::where('id', $reject->user_id)->get();
        $award = $reject->award->acad_code;
        $reject->status = 2;

        $reject->save();

        Notification::send($users, new StudentApplicantStatus($reject->id, $reject->status, $award));
        return redirect()->back();
    }

    public function studentApplicationView($course_code, $id)
    {
        $status = StudentApplicant::with('users')->where('id', $id)->first();
        $grades = Summary::where('app_id', $id)
            ->where('term', "1")
            ->get();
        $grades2 = Summary::where('app_id', $id)
            ->where('term', "2")
            ->get();
        return view('admin.deans-list-award.student', compact('status', 'grades', 'grades2'));
    }

    public function update(Request $request, $course_code, $id)
    {
        $this->validate($request, [
            'status' => 'required',
            'reason' => 'nullable'
        ]);
        $status = StudentApplicant::findOrFail($id);

        $status->status = $request->status;
        $status->reason = $request->reason;
        $award = $status->award->acad_code;

        $users = User::where('id', $status->user_id)->get();
        $status->save();
        if ($request->status == '1' || $request->status == '2') {
            Notification::send($users, new StudentApplicantStatus($status->id, $request->status, $award));
        }

        return redirect()->back()->with('success', 'The Application form updated successfully');
    }

    public function openPdfApproved($course_code, $year_level)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        if ($year_level == "All") {
            $students = StudentApplicant::where('award_applied', '2')
                ->where('course_id', $courses->id)
                ->where('status', '1')
                ->orderBy('gwa', 'asc')
                ->get();
        } else {
            $year = str_replace('-', ' ', $year_level);
            $courses = Courses::where('course_code', $course_code)->first();
            $students = StudentApplicant::where('award_applied', '2')
                ->where('course_id', $courses->id)
                ->where('year_level', $year)
                ->where('status', '1')
                ->orderBy('gwa', 'asc')
                ->get();
        }
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.deans-list-award.student-accepted', array('students' => $students), array('courses' => $courses));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Deans-List-' . $courses->course_code . '.pdf');
    }
    public function openPdfRejected($course_code, $year_level)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        if ($year_level == "All") {
            $students = StudentApplicant::where('award_applied', '2')
                ->where('course_id', $courses->id)
                ->where('status', '2')
                ->orderBy('gwa', 'asc')
                ->get();
        } else {
            $year = str_replace('-', ' ', $year_level);
            $courses = Courses::where('course_code', $course_code)->first();
            $students = StudentApplicant::where('award_applied', '2')
                ->where('course_id', $courses->id)
                ->where('year_level', $year)
                ->where('status', '2')
                ->orderBy('gwa', 'asc')
                ->get();
        }
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.deans-list-award.student-rejected', array('students' => $students), array('courses' => $courses));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Rejected-Deans-List-' . $courses->course_code . '.pdf');
    }

    public function openPdfAll($course_code)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        $students = StudentApplicant::where('award_applied', '2')
            ->where('course_id', $courses->id)
            ->orderBy('year_level', 'asc')
            ->get();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.deans-list-award.student-list', array('students' => $students), array('courses' => $courses));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Deans-List-Applicants-' . $courses->course_code . '.pdf');
    }

    public function overallList(Request $request)
    {
        if ($request->ajax()) {
            if ($request->ajax()) {
                $model = StudentApplicant::with('users', 'courses')->where('award_applied', '2')->select('student_applicants.*');
                if ($request->get('status') == '0' || $request->get('status') == '1' || $request->get('status') == '2') {
                    $model->where('status', $request->get('status'))->get();
                }
                return DataTables::eloquent($model)
                    ->addColumn('checkbox', function (StudentApplicant $stud) {
                        return '<input type="checkbox" name="form_checkbox" data-id="' . $stud['id'] . '">';
                    })
                    ->addColumn('studno', function (StudentApplicant $stud) {
                        return $stud->users->stud_num;
                    })
                    ->addColumn('fname', function (StudentApplicant $stud) {
                        return $stud->users->first_name;
                    })
                    ->addColumn('lname', function (StudentApplicant $stud) {
                        return $stud->users->last_name;
                    })
                    ->addColumn('course', function (StudentApplicant $stud) {
                        return $stud->courses->course_code;
                    })
                    ->addColumn('image', function ($data) {
                        $url = asset('uploads/' . $data->image);
                        return '<img src="' . $url . '" class="img-thumbnail img-circle"
                                width="50" alt="Image">';
                    })
                    ->addColumn('status', function (StudentApplicant $data) {
                        if ($data->status == '1') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else if ($data->status == '2') {
                            return '<span class="badge badge-danger">Rejected</span>';
                        } else {
                            return '<span class="badge badge-warning">Pending</span>';
                        }
                    })
                    ->addColumn('action', function ($data) {
                        return view('admin.deans-list-award.action.buttons', compact('data'));
                    })
                    ->rawColumns(['checkbox', 'image', 'status', 'action'])
                    ->make(true);
            }
        }
        return view('admin.deans-list-award.overall');
    }

    public function destroy(Request $request)
    {
        $form = StudentApplicant::find($request->form_delete_id);
        $form->delete();
        return redirect()->back()->with('success', 'The Application form move to archive successfully');
    }

    public function deleteAll(Request $request)
    {
        $ids = $request->ids;
        StudentApplicant::whereIn('id', explode(",", $ids))->delete();
        return response()->json([
            'success' => 'The Application form move to archive successfully'
        ]);
    }
}
