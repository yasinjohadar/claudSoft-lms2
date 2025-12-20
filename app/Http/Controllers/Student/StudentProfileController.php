<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Http\Requests\Student\ChangePasswordRequest;
use App\Models\Nationality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StudentProfileController extends Controller
{
    /**
     * Display the student's profile.
     */
    public function index()
    {
        $student = auth()->user();

        return view('student.pages.profile.index', compact('student'));
    }

    /**
     * Show the form for editing the student's profile.
     */
    public function edit()
    {
        $student = auth()->user();
        $nationalities = Nationality::all();

        return view('student.pages.profile.edit', compact('student', 'nationalities'));
    }

    /**
     * Update the student's profile information.
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            DB::beginTransaction();

            $student = auth()->user();

            // Update basic information
            $student->name = $request->name;
            $student->email = $request->email;

            if ($request->filled('phone')) {
                $student->phone = $request->phone;
            }

            if ($request->filled('national_id')) {
                $student->national_id = $request->national_id;
            }

            if ($request->filled('date_of_birth')) {
                $student->date_of_birth = $request->date_of_birth;
            }

            if ($request->filled('gender')) {
                $student->gender = $request->gender;
            }

            if ($request->filled('address')) {
                $student->address = $request->address;
            }

            if ($request->filled('city')) {
                $student->city = $request->city;
            }

            if ($request->filled('nationality_id')) {
                $student->nationality_id = $request->nationality_id;
            }

            // Public profile toggle (checked => 1, unchecked => 0)
            // إذا لم يُرسل الحقل فهذا يعني أن السويتش مغلق
            $student->is_profile_public = $request->has('is_profile_public');

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                    Storage::disk('public')->delete($student->photo);
                }

                // Store new photo
                $photoPath = $request->file('photo')->store('profile-photos', 'public');
                $student->photo = $photoPath;
            }

            $student->save();

            DB::commit();

            return redirect()->route('student.profile.index')
                ->with('success', 'تم تحديث الملف الشخصي بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الملف الشخصي: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Change the student's password.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $student = auth()->user();

            // Verify current password
            if (!Hash::check($request->current_password, $student->password)) {
                return redirect()->back()
                    ->with('error', 'كلمة المرور الحالية غير صحيحة')
                    ->withInput();
            }

            // Update password
            $student->password = Hash::make($request->new_password);
            $student->save();

            return redirect()->route('student.profile.index')
                ->with('success', 'تم تغيير كلمة المرور بنجاح');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تغيير كلمة المرور: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete the student's profile photo.
     */
    public function deletePhoto()
    {
        try {
            $student = auth()->user();

            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
                $student->photo = null;
                $student->save();

                return redirect()->back()
                    ->with('success', 'تم حذف الصورة الشخصية بنجاح');
            }

            return redirect()->back()
                ->with('info', 'لا توجد صورة شخصية لحذفها');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الصورة: ' . $e->getMessage());
        }
    }
}
