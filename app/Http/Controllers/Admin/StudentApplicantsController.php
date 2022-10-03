<?php

namespace App\Http\Controllers\Admin;

use App\Models\Courses;
use App\Models\Summary;
use Illuminate\Http\Request;
use App\Models\StudentApplicants;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;

class StudentApplicantsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:menu academic awards', ['only' => ['index', 'achieversView', 'approved', 'rejected', 'studentApplicationView', 'update', 'openPdfApproved', 'openPdfRejected', 'openPdfAll', 'overallList']]);
        $this->middleware('permission:achievers list', ['only' => ['index', 'achieversView', 'approved', 'rejected', 'studentApplicationView', 'update', 'openPdfApproved', 'openPdfRejected', 'openPdfAll', 'overallList']]);
        $this->middleware('permission:achievers edit', ['only' => ['studentApplicationView', 'update', 'approved', 'rejected']]);
        $this->middleware('permission:achievers delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $courses = Courses::all();
        $pending = StudentApplicants::where('award_applied', '1')->where('status', '0')->count();
        return view('admin.achievers-award.index', compact('courses', 'pending'));
    }

    public function achieversView(Request $request, $course_code)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        $model = StudentApplicants::with('users', 'courses')->where('student_applicants.course_id', $courses->id)->where('award_applied', '1')->select('student_applicants.*');

        if ($request->get('status') == '0' || $request->get('status') == '1' || $request->get('status') == '2') {
            $model->where('status', $request->get('status'))->get();
        }
        if ($request->ajax()) {

            return DataTables::eloquent($model)
                ->addColumn('studno', function (StudentApplicants $stud) {
                    return $stud->users->stud_num;
                })
                ->addColumn('fname', function (StudentApplicants $stud) {
                    return $stud->users->first_name;
                })
                ->addColumn('lname', function (StudentApplicants $stud) {
                    return $stud->users->last_name;
                })
                ->addColumn('course', function (StudentApplicants $stud) {
                    return $stud->courses->course_code;
                })
                ->addColumn('image', function ($data) {
                    $url = asset('uploads/' . $data->image);
                    return '<img src="' . $url . '" class="img-thumbnail img-circle"
                                    width="50" alt="Image">';
                })
                ->addColumn('status', function (StudentApplicants $data) {
                    return view('admin.achievers-award.action.status', compact('data'));
                })
                ->addColumn('action', function ($data) {
                    return view('admin.achievers-award.action.buttons', compact('data'));
                })
                ->rawColumns(['image', 'status', 'action'])
                ->make(true);
        }

        return view('admin.achievers-award.view', compact('courses'));
    }

    public function approved($course_code, $id)
    {
        $approve = StudentApplicants::find($id);
        $approve->status = 1;
        $approve->save();
        return redirect()->back();
    }
    public function rejected($course_code, $id)
    {
        $reject = StudentApplicants::find($id);
        $reject->status = 2;
        $reject->save();
        return redirect()->back();
    }

    public function studentApplicationView($course_code, $id)
    {
        $status = StudentApplicants::with('users')->where('id', $id)->first();
        $grades = Summary::where('app_id', $id)
            ->where('term', "1")
            ->get();
        $grades2 = Summary::where('app_id', $id)
            ->where('term', "2")
            ->get();
        return view('admin.achievers-award.student', compact('status', 'grades', 'grades2'));
    }

    public function update(Request $request, $course_code, $id)
    {
        $this->validate($request, [
            'status' => 'required',
            'reason' => 'nullable'
        ]);

        $status = StudentApplicants::findOrFail($id);

        $status->status = $request->status;
        $status->reason = $request->reason;
        $status->save();
        return redirect()->back()->with('success', 'The Application form updated successfully');
    }

    public function openPdfApproved($course_code)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        $students = StudentApplicants::where('award_applied', '1')
            ->where('course_id', $courses->id)
            ->where('status', '1')
            ->orderBy('gwa', 'asc')
            ->get();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.achievers-award.student-accepted', array('students' => $students), array('courses' => $courses));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Achievers-Awardee-' . $courses->course_code . '.pdf');
    }
    public function openPdfRejected($course_code)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        $students = StudentApplicants::where('award_applied', '1')
            ->where('course_id', $courses->id)
            ->where('status', '2')
            ->get();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.achievers-award.student-rejected', array('students' => $students), array('courses' => $courses));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Rejected-Achiever-Awardee-' . $courses->course_code . '.pdf');
    }

    public function openPdfAll($course_code)
    {
        $courses = Courses::where('course_code', $course_code)->first();
        $students = StudentApplicants::where('award_applied', '1')
            ->where('course_id', $courses->id)
            ->orderBy('year_level', 'asc')
            ->get();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.achievers-award.student-list', array('students' => $students), array('courses' => $courses));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Achievers-Awardee-Applicants-' . $courses->course_code . '.pdf');
    }

    public function overallList(Request $request)
    {
        if ($request->ajax()) {
            if ($request->ajax()) {
                $model = StudentApplicants::with('users', 'courses')->where('award_applied', '1')->select('student_applicants.*');

                if ($request->get('status') == '0' || $request->get('status') == '1' || $request->get('status') == '2') {
                    $model->where('status', $request->get('status'))->get();
                }
                return DataTables::eloquent($model)
                    ->addColumn('studno', function (StudentApplicants $stud) {
                        return $stud->users->stud_num;
                    })
                    ->addColumn('fname', function (StudentApplicants $stud) {
                        return $stud->users->first_name;
                    })
                    ->addColumn('lname', function (StudentApplicants $stud) {
                        return $stud->users->last_name;
                    })
                    ->addColumn('course', function (StudentApplicants $stud) {
                        return $stud->courses->course_code;
                    })
                    ->addColumn('image', function ($data) {
                        $url = asset('uploads/' . $data->image);
                        return '<img src="' . $url . '" class="img-thumbnail img-circle"
                                width="50" alt="Image">';
                    })
                    ->addColumn('status', function (StudentApplicants $data) {
                        if ($data->status == '1') {
                            return '<span class="badge badge-success">Approved</span>';
                        } else if ($data->status == '2') {
                            return '<span class="badge badge-danger">Rejected</span>';
                        } else {
                            return '<span class="badge badge-warning">Pending</span>';
                        }
                    })
                    ->addColumn('action', function ($data) {
                        return view('admin.achievers-award.action.buttons', compact('data'));
                    })
                    ->rawColumns(['image', 'status', 'action'])
                    ->make(true);
            }
        }
        return view('admin.achievers-award.overall');
    }

    public function destroy(Request $request)
    {
        $form = StudentApplicants::find($request->form_delete_id);
        if ($form->image) {
            $path = 'uploads/' . $form->image;
            if (File::exists($path)) {
                File::delete($path);
            }
        }
        $form->delete();
        return redirect()->back()->with('success', 'The Application form deleted successfully');
    }
}
