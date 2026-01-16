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
use Illuminate\Support\Facades\Log;
use App\Services\Storage\StorageHelperService;

class StudentProfileController extends Controller
{
    protected StorageHelperService $storageHelper;

    public function __construct(StorageHelperService $storageHelper)
    {
        $this->storageHelper = $storageHelper;
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
            // Email cannot be changed by student

            // Update all fields (including nullable ones)
            $student->name_ar = $request->input('name_ar');
            $student->phone = $request->input('phone');
            $student->national_id = $request->input('national_id');
            $student->date_of_birth = $request->input('date_of_birth');
            $student->gender = $request->input('gender');
            $student->address = $request->input('address');
            $student->nationality_id = $request->input('nationality_id');

            // Public profile toggle (checked => 1, unchecked => 0)
            // إذا لم يُرسل الحقل فهذا يعني أن السويتش مغلق
            $student->is_profile_public = $request->has('is_profile_public');

            // Handle photo upload
            if ($request->hasFile('photo')) {
                Log::info('StudentProfileController: Photo upload started', [
                    'student_id' => $student->id,
                    'file_name' => $request->file('photo')->getClientOriginalName(),
                    'file_size' => $request->file('photo')->getSize(),
                    'file_mime' => $request->file('photo')->getMimeType(),
                ]);

                // Delete old photo if exists
                if ($student->photo) {
                    try {
                        if ($this->storageHelper->fileExists('public', $student->photo)) {
                            $this->storageHelper->deleteFile('public', $student->photo);
                            Log::info('StudentProfileController: Old photo deleted', [
                                'old_photo_path' => $student->photo,
                            ]);
                        }
                    } catch (\Exception $deleteException) {
                        Log::warning('StudentProfileController: Failed to delete old photo', [
                            'old_photo_path' => $student->photo,
                            'error' => $deleteException->getMessage(),
                        ]);
                        // لا نوقف العملية إذا فشل حذف الصورة القديمة
                    }
                }

                // Store new photo using dynamic storage
                $photoPath = null;
                $uploadError = null;

                try {
                    $photoPath = $this->storageHelper->storeUploadedFile('public', 'profile-photos', $request->file('photo'), 'image');
                    
                    if ($photoPath) {
                        Log::info('StudentProfileController: Photo uploaded successfully via dynamic storage', [
                            'photo_path' => $photoPath,
                        ]);

                        // Validate that file actually exists
                        if (!$this->storageHelper->fileExists('public', $photoPath)) {
                            Log::error('StudentProfileController: Photo path returned but file does not exist', [
                                'photo_path' => $photoPath,
                            ]);
                            $photoPath = null;
                            $uploadError = 'تم رفع الصورة لكن الملف غير موجود';
                        }
                    } else {
                        Log::warning('StudentProfileController: Dynamic storage returned false, trying fallback');
                        $uploadError = 'فشل رفع الصورة عبر التخزين الديناميكي';
                    }
                } catch (\Exception $uploadException) {
                    Log::error('StudentProfileController: Exception during dynamic storage upload', [
                        'error' => $uploadException->getMessage(),
                        'trace' => $uploadException->getTraceAsString(),
                    ]);
                    $uploadError = 'خطأ في رفع الصورة: ' . $uploadException->getMessage();
                }

                // Fallback to direct Storage::disk('public') if dynamic storage failed
                if (!$photoPath) {
                    try {
                        Log::info('StudentProfileController: Attempting fallback to Storage::disk("public")');
                        $photoPath = $request->file('photo')->store('profile-photos', 'public');
                        
                        if ($photoPath) {
                            Log::info('StudentProfileController: Photo uploaded successfully via fallback', [
                                'photo_path' => $photoPath,
                            ]);

                            // Validate that file actually exists
                            if (!Storage::disk('public')->exists($photoPath)) {
                                Log::error('StudentProfileController: Fallback photo path returned but file does not exist', [
                                    'photo_path' => $photoPath,
                                ]);
                                throw new \Exception('تم رفع الصورة لكن الملف غير موجود');
                            }
                        } else {
                            throw new \Exception('فشل في رفع الصورة عبر التخزين المحلي');
                        }
                    } catch (\Exception $fallbackException) {
                        Log::error('StudentProfileController: Fallback storage also failed', [
                            'error' => $fallbackException->getMessage(),
                            'original_error' => $uploadError,
                        ]);
                        throw new \Exception('فشل في رفع الصورة. ' . ($uploadError ?? $fallbackException->getMessage()));
                    }
                }

                // Set photo path if upload was successful
                if ($photoPath) {
                    $student->photo = $photoPath;
                    Log::info('StudentProfileController: Photo path saved to student profile', [
                        'student_id' => $student->id,
                        'photo_path' => $photoPath,
                    ]);
                } else {
                    throw new \Exception('فشل في رفع الصورة. يرجى المحاولة مرة أخرى.');
                }
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

            if ($student->photo && $this->storageHelper->fileExists('public', $student->photo)) {
                $this->storageHelper->deleteFile('public', $student->photo);
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
