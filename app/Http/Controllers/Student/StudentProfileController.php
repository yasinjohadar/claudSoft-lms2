<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Services\Auth\PhoneOtpService;
use App\Enums\OtpPurpose;
use App\Http\Requests\Student\ChangePasswordRequest;
use App\Models\Nationality;
use App\Services\Student\StudentProfileCompletionService;
use App\Services\Student\StudentProfilePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StudentProfileController extends Controller
{
    public function __construct(
        protected StudentProfilePhotoService $profilePhotoService,
        protected StudentProfileCompletionService $profileCompletion,
    ) {}

    /**
     * Upload only the profile photo (separate from full profile update)
     */
    public function uploadPhoto(Request $request)
    {
        try {
            $request->validate([
                'photo' => [
                    'required',
                    'image',
                    'mimes:jpeg,jpg,png,gif,webp',
                    'max:2048',
                ],
            ], [
                'photo.required' => 'يرجى اختيار صورة',
                'photo.image' => 'الملف يجب أن يكون صورة',
                'photo.mimes' => 'الصورة يجب أن تكون بصيغة: jpeg, jpg, png, gif, webp',
                'photo.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
            ]);

            DB::beginTransaction();

            $student = auth()->user();
            $photoPath = $this->profilePhotoService->store($student, $request->file('photo'));

            if (! $photoPath) {
                throw new \Exception('فشل في رفع الصورة إلى التخزين السحابي');
            }

            $student->photo = $photoPath;
            $student->avatar = $photoPath;
            $student->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'تم تحديث الصورة الشخصية بنجاح');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('StudentProfileController: Photo upload failed', [
                'student_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء رفع الصورة: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the student's profile.
     */
    public function index()
    {
        $student = auth()->user()->load('nationality');

        return view('student.pages.profile.index', compact('student'));
    }

    /**
     * Show the form for editing the student's profile.
     */
    public function edit()
    {
        $student = auth()->user();
        $nationalities = Nationality::all();
        $profileLocked = $this->profileCompletion->isLockedFor($student);

        return view('student.pages.profile.edit', compact('student', 'nationalities', 'profileLocked'));
    }

    /**
     * Update the student's profile information.
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            DB::beginTransaction();

            $student = auth()->user();

            $phoneChanged = $request->input('country_code') !== $student->country_code
                || $request->input('phone') !== $student->phone;

            $otpService = app(PhoneOtpService::class);
            if ($phoneChanged && $otpService->isAvailableFor(OtpPurpose::ChangePhone)) {
                $fullPhone = $otpService->formatPhoneDisplay(
                    (string) $request->input('country_code'),
                    (string) $request->input('phone')
                );

                try {
                    $otpService->send($fullPhone, OtpPurpose::ChangePhone, $student, $request->ip());
                } catch (\InvalidArgumentException $e) {
                    return redirect()->back()->withInput()->with('error', $e->getMessage());
                }

                session([
                    'pending_phone_change' => [
                        'country_code' => $request->input('country_code'),
                        'phone' => $request->input('phone'),
                        'full_phone_digits' => $otpService->normalizePhone($fullPhone),
                    ],
                    'pending_profile_update' => $request->except(['_token', '_method']),
                ]);

                return redirect()->route('phone-otp.verify', [
                    'purpose' => OtpPurpose::ChangePhone->value,
                    'phone' => $otpService->normalizePhone($fullPhone),
                ])->with('status', 'تم إرسال رمز التحقق للرقم الجديد.');
            }

            $student->name = $request->name;
            $student->name_ar = $request->input('name_ar');
            $student->country_code = $request->input('country_code');
            $student->phone = $request->input('phone');
            $student->date_of_birth = $request->input('date_of_birth');
            $student->gender = $request->input('gender');
            $student->city = $request->input('city');
            $student->address = $request->input('address');
            $student->nationality_id = $request->input('nationality_id');
            $student->is_profile_public = $request->has('is_profile_public');

            $student->save();

            DB::commit();

            $student->refresh();

            if ($this->profileCompletion->isEnforcementEnabled() && $this->profileCompletion->isComplete($student)) {
                return redirect()->route('student.dashboard')
                    ->with('success', 'تم إكمال ملفك الشخصي بنجاح! يمكنك الآن استخدام المنصة.');
            }

            return redirect()->route('student.profile.index')
                ->with('success', 'تم تحديث الملف الشخصي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('StudentProfileController: Update failed', [
                'student_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

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

            if (! Hash::check($request->current_password, $student->password)) {
                return redirect()->back()
                    ->with('error', 'كلمة المرور الحالية غير صحيحة')
                    ->withInput();
            }

            $student->password = Hash::make($request->new_password);
            $student->save();

            if ($this->profileCompletion->isLockedFor($student)) {
                return redirect()->route('student.profile.edit')
                    ->with('success', 'تم تغيير كلمة المرور بنجاح');
            }

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

            if (! $student->photo && ! $student->avatar) {
                return redirect()->back()
                    ->with('info', 'لا توجد صورة شخصية لحذفها');
            }

            $this->profilePhotoService->deleteForUser($student);

            $student->photo = null;
            $student->avatar = null;
            $student->save();

            return redirect()->back()
                ->with('success', 'تم حذف الصورة الشخصية بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الصورة: ' . $e->getMessage());
        }
    }
}
